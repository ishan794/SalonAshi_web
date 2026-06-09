<?php

namespace App\Modules\Api\Controllers;

class DashboardController extends ApiBaseController
{
    /**
     * GET /api/dashboard
     * Admin/manager: salon-wide KPIs.
     * Stylist: own appointments count + own today revenue.
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $db      = db_connect();
        $today   = date('Y-m-d');
        $todayFrom = $today . ' 00:00:00';
        $todayTo   = $today . ' 23:59:59';

        if ($this->apiUser->isStylist() && ($staffId = $this->apiUser->staffId())) {
            // Stylist view — own appointments + own revenue today
            $apptToday = (int) $db->table('appointments')
                ->where('staff_id', $staffId)
                ->where('DATE(start_at)', $today)
                ->whereNotIn('status', ['cancelled'])
                ->countAllResults();

            $revRow = $db->table('payments p')
                ->selectSum('p.amount', 'revenue')
                ->join('invoices i', 'i.id = p.invoice_id')
                ->join('appointments ia', 'ia.id = i.appointment_id', 'left')
                ->where('p.status', 'success')
                ->where('p.paid_at >=', $todayFrom)
                ->where('p.paid_at <=', $todayTo)
                ->groupStart()
                    ->where('i.staff_id', $staffId)
                    ->orWhere('ia.staff_id', $staffId)
                ->groupEnd()
                ->get()->getRowArray();

            $todayAppts = $db->table('appointments a')
                ->select('a.*, c.full_name AS customer_name')
                ->join('customers c', 'c.id = a.customer_id', 'left')
                ->where('a.staff_id', $staffId)
                ->where('DATE(a.start_at)', $today)
                ->whereNotIn('a.status', ['cancelled'])
                ->orderBy('a.start_at', 'asc')
                ->get()->getResultArray();

            return $this->ok([
                'today_appointments' => $apptToday,
                'today_revenue'      => (float) ($revRow['revenue'] ?? 0),
                'agenda'             => $todayAppts,
            ]);
        }

        // Admin / manager / receptionist — full salon view
        $statsRow = $db->table('appointments')
            ->select("
                COUNT(*) AS total,
                SUM(DATE(start_at) = '{$today}') AS today,
                SUM(status = 'pending') AS pending,
                SUM(status = 'completed') AS completed
            ")
            ->get()->getRowArray();

        $revToday = (float) ($db->table('payments')
            ->selectSum('amount', 'revenue')
            ->where('status', 'success')
            ->where('paid_at >=', $todayFrom)
            ->where('paid_at <=', $todayTo)
            ->get()->getRowArray()['revenue'] ?? 0);

        $revMonth = (float) ($db->table('payments')
            ->selectSum('amount', 'revenue')
            ->where('status', 'success')
            ->where('DATE(paid_at) >=', date('Y-m-01'))
            ->get()->getRowArray()['revenue'] ?? 0);

        $pendingBalance = (float) ($db->table('invoices')
            ->selectSum('balance', 'bal')
            ->where('balance >', 0)
            ->get()->getRowArray()['bal'] ?? 0);

        $totalCustomers = (int) $db->table('customers')->countAllResults();
        $activeStaff    = (int) $db->table('staff')->where('is_active', 1)->countAllResults();

        $agenda = $db->table('appointments a')
            ->select('a.id, a.code, a.start_at, a.end_at, a.status, c.full_name AS customer_name, s.full_name AS staff_name')
            ->join('customers c', 'c.id = a.customer_id', 'left')
            ->join('staff s', 's.id = a.staff_id', 'left')
            ->where('DATE(a.start_at)', $today)
            ->whereNotIn('a.status', ['cancelled'])
            ->orderBy('a.start_at', 'asc')
            ->limit(20)
            ->get()->getResultArray();

        return $this->ok([
            'appointments' => [
                'total'     => (int) ($statsRow['total'] ?? 0),
                'today'     => (int) ($statsRow['today'] ?? 0),
                'pending'   => (int) ($statsRow['pending'] ?? 0),
                'completed' => (int) ($statsRow['completed'] ?? 0),
            ],
            'revenue' => [
                'today' => $revToday,
                'month' => $revMonth,
            ],
            'pending_balance'  => $pendingBalance,
            'total_customers'  => $totalCustomers,
            'active_staff'     => $activeStaff,
            'today_agenda'     => $agenda,
        ]);
    }
}
