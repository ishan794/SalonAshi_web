<?php

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $today = date('Y-m-d');

        $stats = [
            'today_appts'     => (int) $db->table('appointments')->where('DATE(start_at)', $today)->countAllResults(),
            'today_revenue'   => (float) ($db->table('payments')->selectSum('amount')->where('DATE(paid_at)', $today)->where('status','success')->get()->getRow('amount') ?? 0),
            'pending_balance' => (float) ($db->table('invoices')->selectSum('balance')->where('balance >', 0)->get()->getRow('balance') ?? 0),
            'total_customers' => (int) $db->table('customers')->countAllResults(),
            'total_staff'     => (int) $db->table('staff')->where('is_active', 1)->countAllResults(),
            'total_services'  => (int) $db->table('services')->where('is_active', 1)->countAllResults(),
        ];

        $recentCustomers = $db->table('customers')->orderBy('id','desc')->limit(5)->get()->getResultArray();



        // ── Today's agenda ──
        $todayAgenda = $db->table('appointments a')
            ->select('a.id, a.code, a.start_at, a.status, c.full_name AS customer_name, st.full_name AS staff_name')
            ->join('customers c', 'c.id = a.customer_id', 'left')
            ->join('staff st',    'st.id = a.staff_id',   'left')
            ->where('DATE(a.start_at)', $today)
            ->whereNotIn('a.status', ['cancelled'])
            ->orderBy('a.start_at', 'asc')
            ->get()->getResultArray();

        // Show only the next 5 upcoming appointments; if the day is already over,
        // fall back to the last 5 so the widget isn't empty.
        $now            = date('Y-m-d H:i:s');
        $upcoming       = array_values(array_filter($todayAgenda, static fn ($a) => $a['start_at'] >= $now));
        $todayAgendaTop = $upcoming ? array_slice($upcoming, 0, 5) : array_slice($todayAgenda, -5);
        $todayAgendaTotal = count($todayAgenda);

        // ── Top customers by lifetime spend (paid invoices) ──
        $topCustomers = $db->table('payments p')
            ->select('c.id, c.full_name, c.mobile, SUM(p.amount) spend')
            ->join('invoices i', 'i.id = p.invoice_id', 'left')
            ->join('customers c', 'c.id = i.customer_id', 'left')
            ->where('p.status', 'success')
            ->where('c.id IS NOT NULL', null, false)
            ->groupBy('c.id, c.full_name, c.mobile')
            ->orderBy('spend', 'DESC')
            ->limit(5)->get()->getResultArray();

        // ── Calendar data ──
        $month = $this->request->getGet('month');
        // Sanity: month must be YYYY-MM
        if (! $month || ! preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');

        $monthStart = $month . '-01';
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        $monthAppts = $db->table('appointments a')
            ->select('a.id, a.code, a.start_at, a.end_at, a.status, c.full_name AS customer_name, s.full_name AS staff_name')
            ->join('customers c', 'c.id = a.customer_id', 'left')
            ->join('staff s',     's.id = a.staff_id',    'left')
            ->where('a.start_at >=', $monthStart . ' 00:00:00')
            ->where('a.start_at <=', $monthEnd   . ' 23:59:59')
            ->orderBy('a.start_at', 'asc')
            ->get()->getResultArray();

        // Group by Y-m-d
        $apptsByDay = [];
        foreach ($monthAppts as $a) {
            $d = date('Y-m-d', strtotime($a['start_at']));
            $apptsByDay[$d][] = [
                'id'             => (int) $a['id'],
                'code'           => $a['code'],
                'start_at'       => $a['start_at'],
                'end_at'         => $a['end_at'],
                'time'           => date('H:i', strtotime($a['start_at'])),
                'status'         => $a['status'],
                'customer_name'  => $a['customer_name'] ?: 'Walk-in',
                'staff_name'     => $a['staff_name'] ?: '—',
            ];
        }

        return view('layout/admin', [
            'title'   => 'Dashboard',
            'content' => view('App\Modules\Dashboard\Views\index', [
                'stats'           => $stats,
                'recentCustomers' => $recentCustomers,
                'month'           => $month,
                'monthStart'      => $monthStart,
                'apptsByDay'      => $apptsByDay,
                'totalMonthAppts' => count($monthAppts),
                'todayAgenda'      => $todayAgendaTop,
                'todayAgendaTotal' => $todayAgendaTotal,
                'topCustomers'    => $topCustomers,
            ]),
        ]);
    }
}
