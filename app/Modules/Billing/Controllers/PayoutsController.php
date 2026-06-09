<?php

namespace App\Modules\Billing\Controllers;

use App\Controllers\BaseController;
use App\Modules\Reports\Models\ReportRepository;

/**
 * Salon-wide commission payout overview — every active stylist's revenue +
 * commission for a chosen period, with per-stylist PDF statements.
 * Reuses ReportRepository::staffPerformance() (payment-driven revenue).
 */
class PayoutsController extends BaseController
{
    public function index()
    {
        [$from, $to, $preset] = $this->range();
        $rows = (new ReportRepository())->staffPerformance($from, $to);

        foreach ($rows as &$r) {
            $r['commission'] = round(((float) $r['revenue']) * ((float) $r['commission_pct']) / 100, 2);
        }
        unset($r);

        $totalRevenue    = array_sum(array_column($rows, 'revenue'));
        $totalCommission = array_sum(array_column($rows, 'commission'));

        return view('layout/admin', [
            'title'   => 'Payouts',
            'content' => view('App\Modules\Billing\Views\payouts', [
                'rows' => $rows, 'from' => $from, 'to' => $to, 'preset' => $preset,
                'totalRevenue' => $totalRevenue, 'totalCommission' => $totalCommission,
            ]),
        ]);
    }

    private function range(): array
    {
        $preset = $this->request->getGet('preset') ?: 'mtd';
        $from   = $this->request->getGet('from');
        $to     = $this->request->getGet('to');
        if ($preset === 'custom' && $from && $to) return [$from, $to, 'custom'];

        [$f, $t] = match ($preset) {
            'today' => [date('Y-m-d'), date('Y-m-d')],
            '7d'    => [date('Y-m-d', strtotime('-6 days')), date('Y-m-d')],
            '30d'   => [date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
            'ytd'   => [date('Y-01-01'), date('Y-m-d')],
            default => [date('Y-m-01'), date('Y-m-d')], // mtd
        };
        return [$f, $t, $preset];
    }
}
