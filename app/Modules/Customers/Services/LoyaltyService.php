<?php

namespace App\Modules\Customers\Services;

use App\Modules\Settings\Models\SettingModel;

/**
 * Loyalty: earn on paid invoices, redeem at checkout, auto-upgrade tier.
 * All rates + thresholds read from the `settings` table (Settings → Loyalty).
 */
class LoyaltyService
{
    private SettingModel $s;

    public function __construct()
    {
        $this->s = new SettingModel();
    }

    public function isEnabled(): bool
    {
        return (string) $this->s->get('loyalty_enabled', '1') === '1';
    }

    public function earnPerLkr(): float
    {
        return (float) $this->s->get('loyalty_earn_per_lkr', '0.01'); // pts per LKR
    }

    public function redeemValue(): float
    {
        return (float) $this->s->get('loyalty_redeem_value', '0.5');   // LKR per pt
    }

    public function minRedeem(): int
    {
        return (int) $this->s->get('loyalty_min_redeem_pts', '50');
    }

    public function tierThresholds(): array
    {
        return [
            'silver'   => (int) $this->s->get('loyalty_tier_silver_pts', '500'),
            'gold'     => (int) $this->s->get('loyalty_tier_gold_pts',   '1500'),
            'platinum' => (int) $this->s->get('loyalty_tier_platinum_pts','5000'),
        ];
    }

    public function tierDiscount(string $tier): float
    {
        return (float) $this->s->get('loyalty_tier_' . $tier . '_disc', '0');
    }

    /** Current points balance for a customer (sum of all txns) */
    public function balance(int $customerId): int
    {
        return (int) (db_connect()
            ->table('loyalty_transactions')
            ->selectSum('points')
            ->where('customer_id', $customerId)
            ->get()->getRow('points') ?? 0);
    }

    /** Lifetime earned (positive earn txns only) — used for tier calculation */
    public function lifetimeEarned(int $customerId): int
    {
        return (int) (db_connect()
            ->table('loyalty_transactions')
            ->selectSum('points')
            ->where('customer_id', $customerId)
            ->where('type', 'earn')
            ->get()->getRow('points') ?? 0);
    }

    /** Recent transactions */
    public function recent(int $customerId, int $limit = 20): array
    {
        return db_connect()
            ->table('loyalty_transactions')
            ->where('customer_id', $customerId)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /**
     * Credit points for an invoice that was just fully paid.
     * Returns the number of points awarded (0 if loyalty off or amount=0).
     */
    public function earn(int $customerId, int $invoiceId, float $amountPaid): int
    {
        if (! $this->isEnabled() || $amountPaid <= 0 || $customerId <= 0) return 0;

        // Don't double-credit the same invoice
        $alreadyEarned = (int) db_connect()
            ->table('loyalty_transactions')
            ->where('invoice_id', $invoiceId)
            ->where('type', 'earn')
            ->countAllResults();
        if ($alreadyEarned > 0) return 0;

        $pts = (int) floor($amountPaid * $this->earnPerLkr());
        if ($pts <= 0) return 0;

        $this->writeTxn($customerId, 'earn', $pts, $invoiceId, 'Earned on invoice #' . $invoiceId);
        $this->recalcTier($customerId);
        $this->syncCustomerBalance($customerId);
        return $pts;
    }

    /**
     * Redeem points against an invoice — adds a discount adjustment on the invoice.
     * Returns the LKR discount applied (or 0 if blocked).
     */
    public function redeem(int $invoiceId, int $points): array
    {
        if (! $this->isEnabled() || $points <= 0) return ['ok' => false, 'msg' => 'Loyalty disabled or zero points.'];
        if ($points < $this->minRedeem()) {
            return ['ok' => false, 'msg' => 'Minimum redemption is ' . $this->minRedeem() . ' points.'];
        }

        $db = db_connect();
        $inv = $db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
        if (! $inv) return ['ok' => false, 'msg' => 'Invoice not found.'];

        $customerId = (int) $inv['customer_id'];
        $balance    = $this->balance($customerId);

        if ($points > $balance) {
            return ['ok' => false, 'msg' => "Customer only has $balance points."];
        }

        $discountLkr = $points * $this->redeemValue();
        // Cap discount at the unpaid balance so we don't overdiscount
        $maxDiscount = (float) $inv['total'] - (float) $inv['discount'] - (float) $inv['paid'];
        if ($discountLkr > $maxDiscount) {
            $allowedPts = (int) floor($maxDiscount / $this->redeemValue());
            return ['ok' => false, 'msg' => "Only $allowedPts points can be used on this invoice (LKR " . number_format($maxDiscount, 0) . ' max).'];
        }

        // Apply discount to invoice
        $db->table('invoices')
            ->where('id', $invoiceId)
            ->update(['discount' => (float) $inv['discount'] + $discountLkr]);

        // Debit points
        $this->writeTxn($customerId, 'redeem', -$points, $invoiceId,
            'Redeemed against invoice ' . $inv['invoice_no'] . ' (LKR ' . number_format($discountLkr, 0) . ')');
        $this->syncCustomerBalance($customerId);

        // Recalc invoice totals
        (new \App\Modules\Billing\Models\InvoiceModel())->recalcTotals($invoiceId);

        return ['ok' => true, 'msg' => 'Redeemed ' . $points . ' pts → LKR ' . number_format($discountLkr, 0) . ' off.', 'discount' => $discountLkr];
    }

    /** Manual adjustment (admin add/subtract) */
    public function adjust(int $customerId, int $points, ?string $note = null): int
    {
        if ($points === 0) return 0;
        $this->writeTxn($customerId, 'adjust', $points, null, $note ?: 'Manual adjustment');
        $this->recalcTier($customerId);
        $this->syncCustomerBalance($customerId);
        return $points;
    }

    /** Recalculate tier from lifetime earned points */
    public function recalcTier(int $customerId): string
    {
        $lifetime = $this->lifetimeEarned($customerId);
        $t = $this->tierThresholds();

        $tier = 'none';
        if ($lifetime >= $t['platinum'])     $tier = 'platinum';
        elseif ($lifetime >= $t['gold'])     $tier = 'gold';
        elseif ($lifetime >= $t['silver'])   $tier = 'silver';

        db_connect()->table('customers')
            ->where('id', $customerId)
            ->update(['membership' => $tier]);
        return $tier;
    }

    // ───── internals ─────

    private function writeTxn(int $customerId, string $type, int $points, ?int $invoiceId, string $note): void
    {
        $bal = $this->balance($customerId) + $points;
        db_connect()->table('loyalty_transactions')->insert([
            'customer_id'   => $customerId,
            'type'          => $type,
            'points'        => $points,
            'invoice_id'    => $invoiceId,
            'note'          => $note,
            'balance_after' => max(0, $bal),
            'created_by'    => session('user.id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /** Keep customers.loyalty_points in sync with the txn ledger */
    private function syncCustomerBalance(int $customerId): void
    {
        db_connect()->table('customers')
            ->where('id', $customerId)
            ->update(['loyalty_points' => max(0, $this->balance($customerId))]);
    }
}
