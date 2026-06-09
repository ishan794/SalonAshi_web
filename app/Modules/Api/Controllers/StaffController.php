<?php

namespace App\Modules\Api\Controllers;

use App\Modules\Staff\Models\StaffModel;
use App\Modules\Staff\Models\StaffPayoutModel;

class StaffController extends ApiBaseController
{
    /**
     * GET /api/staff — admin/manager only.
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->apiUser->isAdmin()) return $this->forbidden('Staff list is admin/manager only.');

        $staff = (new StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll();
        return $this->ok($staff);
    }

    /**
     * GET /api/staff/{id}
     * Stylists can only see themselves.
     */
    public function show(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist() && $this->apiUser->staffId() !== $id) {
            return $this->forbidden();
        }

        $model = new StaffModel();
        $staff = $model->find($id);
        if (! $staff) return $this->notFound('Staff member not found.');

        // Attached services
        $services = $model->servicesFor($id);

        return $this->ok(['staff' => $staff, 'services' => $services]);
    }

    /**
     * GET /api/staff/{id}/schedule
     * Returns the 7-day weekly schedule.
     */
    public function schedule(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist() && $this->apiUser->staffId() !== $id) {
            return $this->forbidden();
        }

        $model = new StaffModel();
        $staff = $model->find($id);
        if (! $staff) return $this->notFound('Staff member not found.');

        return $this->ok([
            'staff_id' => $id,
            'schedule' => $model->getSchedule($id),
            'time_off' => $model->getTimeOff($id, date('Y-m-d')),
        ]);
    }

    /**
     * GET /api/staff/{id}/payouts?from=&to=
     * Stylists can only see own payouts.
     */
    public function payouts(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist() && $this->apiUser->staffId() !== $id) {
            return $this->forbidden();
        }

        $model = new StaffModel();
        $staff = $model->find($id);
        if (! $staff) return $this->notFound('Staff member not found.');

        // Recorded payout records
        $payouts = (new StaffPayoutModel())->forStaff($id);

        // Calculated commission from payments (for the summary)
        [$from, $to] = $this->dateRange();
        $db = db_connect();
        $rows = $db->table('payments p')
            ->select("DATE_FORMAT(p.paid_at, '%Y-%m') AS period, COUNT(p.id) AS payments_n, SUM(p.amount) AS revenue")
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('appointments ia', 'ia.id = i.appointment_id', 'left')
            ->where('p.status', 'success')
            ->where('p.paid_at >=', $from . ' 00:00:00')
            ->where('p.paid_at <=', $to . ' 23:59:59')
            ->groupStart()->where('i.staff_id', $id)->orWhere('ia.staff_id', $id)->groupEnd()
            ->groupBy("DATE_FORMAT(p.paid_at, '%Y-%m')")
            ->orderBy('period', 'DESC')
            ->get()->getResultArray();

        $pct = (float) ($staff['commission_pct'] ?? 0);
        foreach ($rows as &$r) {
            $r['commission'] = round((float) $r['revenue'] * $pct / 100, 2);
        }
        unset($r);

        return $this->ok([
            'staff'             => $staff,
            'from'              => $from,
            'to'                => $to,
            'commission_pct'    => $pct,
            'revenue_by_month'  => $rows,
            'total_revenue'     => array_sum(array_column($rows, 'revenue')),
            'total_commission'  => array_sum(array_column($rows, 'commission')),
            'recorded_payouts'  => $payouts,
        ]);
    }

    /**
     * GET /api/staff/{id}/revenue?from=&to=
     * Stylists can only see own revenue.
     */
    public function revenue(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist() && $this->apiUser->staffId() !== $id) {
            return $this->forbidden();
        }

        $model = new StaffModel();
        $staff = $model->find($id);
        if (! $staff) return $this->notFound('Staff member not found.');

        [$from, $to] = $this->dateRange();
        $db = db_connect();

        $lines = $db->table('payments p')
            ->select('p.paid_at, p.amount, p.method, i.invoice_no, c.full_name AS customer_name')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('customers c', 'c.id = i.customer_id', 'left')
            ->join('appointments ia', 'ia.id = i.appointment_id', 'left')
            ->where('p.status', 'success')
            ->where('p.paid_at >=', $from . ' 00:00:00')
            ->where('p.paid_at <=', $to . ' 23:59:59')
            ->groupStart()->where('i.staff_id', $id)->orWhere('ia.staff_id', $id)->groupEnd()
            ->orderBy('p.paid_at', 'DESC')
            ->get()->getResultArray();

        $apptStats = $db->table('appointments')
            ->select("COUNT(*) total, SUM(status='completed') completed, SUM(status='no_show') no_shows")
            ->where('staff_id', $id)
            ->where('start_at >=', $from . ' 00:00:00')
            ->where('start_at <=', $to . ' 23:59:59')
            ->get()->getRowArray();

        $revenue   = array_sum(array_map(fn($l) => (float) $l['amount'], $lines));
        $avgTicket = ! empty($lines) ? $revenue / count($lines) : 0;

        return $this->ok([
            'staff'       => $staff,
            'from'        => $from,
            'to'          => $to,
            'revenue'     => $revenue,
            'avg_ticket'  => round($avgTicket, 2),
            'payments'    => $lines,
            'appt_stats'  => $apptStats,
        ]);
    }

    /** Shared date-range helper (?preset=…, ?from=&to=, default 30d). */
    private function dateRange(): array
    {
        $preset = $this->request->getGet('preset') ?: '30d';
        $from   = $this->request->getGet('from');
        $to     = $this->request->getGet('to');
        if ($preset === 'custom' && $from && $to) return [$from, $to];
        return match ($preset) {
            'today' => [date('Y-m-d'), date('Y-m-d')],
            '7d'    => [date('Y-m-d', strtotime('-6 days')), date('Y-m-d')],
            'mtd'   => [date('Y-m-01'), date('Y-m-d')],
            'ytd'   => [date('Y-01-01'), date('Y-m-d')],
            default => [date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
        };
    }
}
