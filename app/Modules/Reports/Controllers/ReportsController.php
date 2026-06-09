<?php

namespace App\Modules\Reports\Controllers;

use App\Controllers\BaseController;
use App\Modules\Reports\Models\ReportRepository;

class ReportsController extends BaseController
{
    private ReportRepository $r;

    public function __construct()
    {
        $this->r = new ReportRepository();
    }

    public function index()    { return redirect()->to('/admin/reports/overview'); }

    public function overview() { return $this->render('overview', 'Overview',           $this->overviewData(...func_get_args())); }
    public function sales()    { return $this->render('sales',    'Sales',              $this->salesData(...func_get_args())); }
    public function services() { return $this->render('services', 'Services',           $this->servicesData(...func_get_args())); }
    public function staff()    { return $this->render('staff',    'Staff performance',  $this->staffData(...func_get_args())); }

    // ───── CSV downloads ─────

    public function csvSales()
    {
        [$from, $to] = $this->range();
        $rows = $this->r->dailyRevenue($from, $to);
        return $this->csvOut("sales-{$from}_to_{$to}.csv", $rows, ['date', 'revenue']);
    }

    public function csvServices()
    {
        [$from, $to] = $this->range();
        $rows = $this->r->topServices($from, $to, 500);
        return $this->csvOut("services-{$from}_to_{$to}.csv", $rows, ['service', 'bookings', 'revenue', 'total_minutes']);
    }

    public function csvStaff()
    {
        [$from, $to] = $this->range();
        $rows = $this->r->staffPerformance($from, $to);
        // Add commission column
        foreach ($rows as &$r) {
            $r['commission'] = round(((float)$r['revenue']) * ((float)$r['commission_pct']) / 100, 2);
        }
        unset($r);
        return $this->csvOut("staff-{$from}_to_{$to}.csv", $rows,
            ['full_name','role','appointments_n','completed_n','no_shows_n','revenue','commission_pct','commission']);
    }

    /** Per-stylist commission payout statement (PDF). */
    public function payout(int $staffId)
    {
        [$from, $to] = $this->range();
        $db = db_connect();

        $staff = $db->table('staff')->where('id', $staffId)->get()->getRowArray();
        if (! $staff) return redirect()->to('/admin/reports/staff')->with('flash_error', 'Stylist not found.');

        [$f, $t] = [$from . ' 00:00:00', $to . ' 23:59:59'];

        // Line-level breakdown: paid services attributed to this stylist in range.
        $lines = $db->table('payments p')
            ->select('p.paid_at, p.amount, i.invoice_no AS invoice_code, c.full_name AS customer_name')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('customers c', 'c.id = i.customer_id', 'left')
            ->join('appointments ia', 'ia.id = i.appointment_id', 'left')
            ->where('p.status', 'success')
            ->where('p.paid_at >=', $f)->where('p.paid_at <=', $t)
            ->groupStart()->where('i.staff_id', $staffId)->orWhere('ia.staff_id', $staffId)->groupEnd()
            ->orderBy('p.paid_at', 'ASC')
            ->get()->getResultArray();

        $revenue   = array_sum(array_map(fn($l) => (float) $l['amount'], $lines));
        $pct       = (float) ($staff['commission_pct'] ?? 0);
        $commission = round($revenue * $pct / 100, 2);

        $s        = new \App\Modules\Settings\Models\SettingModel();
        $salon    = $s->get('salon_name', 'SalonCMS');
        $currency = $s->get('currency_symbol') ?: 'LKR';

        $html = view('App\Modules\Reports\Views\payout_pdf', compact(
            'staff', 'lines', 'revenue', 'pct', 'commission', 'from', 'to', 'salon', 'currency'
        ));

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        helper('system');
        log_action('report.payout', ['entity_type' => 'staff', 'entity_id' => $staffId, 'description' => 'Generated payout statement for ' . $staff['full_name'] . " ({$from}–{$to})"]);

        $fname = 'payout-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($staff['full_name'])) . "-{$from}_to_{$to}.pdf";
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fname . '"')
            ->setBody($dompdf->output());
    }

    // ───── internals ─────

    private function render(string $tab, string $title, array $data)
    {
        [$from, $to, $preset] = [$data['from'], $data['to'], $data['preset']];
        return view('layout/admin', [
            'title'   => 'Reports · ' . $title,
            'content' => view('App\Modules\Reports\Views\layout', [
                'active'  => $tab,
                'subview' => 'App\Modules\Reports\Views\tab_' . $tab,
                'from'    => $from,
                'to'      => $to,
                'preset'  => $preset,
                'data'    => $data,
            ]),
        ]);
    }

    private function range(): array
    {
        $preset = $this->request->getGet('preset') ?: '30d';
        $from   = $this->request->getGet('from');
        $to     = $this->request->getGet('to');

        if ($preset !== 'custom' || ! $from || ! $to) {
            [$from, $to] = match ($preset) {
                'today' => [date('Y-m-d'), date('Y-m-d')],
                '7d'    => [date('Y-m-d', strtotime('-6 days')), date('Y-m-d')],
                '30d'   => [date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
                'mtd'   => [date('Y-m-01'), date('Y-m-d')],
                'ytd'   => [date('Y-01-01'), date('Y-m-d')],
                default => [date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
            };
            $preset = $preset ?: '30d';
        }

        // Sanity
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-d', strtotime('-29 days'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');
        if ($from > $to) [$from, $to] = [$to, $from];

        return [$from, $to, $preset];
    }

    private function overviewData(): array
    {
        [$from, $to, $preset] = $this->range();
        return [
            'from' => $from, 'to' => $to, 'preset' => $preset,
            'kpis' => $this->r->overviewKpis($from, $to),
            'daily' => $this->r->dailyRevenue($from, $to),
        ];
    }
    private function salesData(): array
    {
        [$from, $to, $preset] = $this->range();
        return [
            'from' => $from, 'to' => $to, 'preset' => $preset,
            'totals'  => $this->r->invoiceTotals($from, $to),
            'methods' => $this->r->paymentMethodBreakdown($from, $to),
            'daily'   => $this->r->dailyRevenue($from, $to),
        ];
    }
    private function servicesData(): array
    {
        [$from, $to, $preset] = $this->range();
        return [
            'from' => $from, 'to' => $to, 'preset' => $preset,
            'rows' => $this->r->topServices($from, $to, 50),
        ];
    }
    private function staffData(): array
    {
        [$from, $to, $preset] = $this->range();
        $rows = $this->r->staffPerformance($from, $to);
        // Compute commission on the fly
        foreach ($rows as &$r) {
            $r['commission'] = round(((float)$r['revenue']) * ((float)$r['commission_pct']) / 100, 2);
        }
        unset($r);
        return [
            'from' => $from, 'to' => $to, 'preset' => $preset,
            'rows' => $rows,
        ];
    }

    private function csvOut(string $filename, array $rows, array $headers)
    {
        $csv = ReportRepository::toCsv($rows, $headers);
        return $this->response
            ->setContentType('text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }
}
