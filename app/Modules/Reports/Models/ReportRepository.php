<?php

namespace App\Modules\Reports\Models;

/**
 * Aggregates the queries used across the Reports tabs.
 * All methods accept (string $from, string $to) in 'YYYY-MM-DD' format (inclusive).
 */
class ReportRepository
{
    private function db() { return db_connect(); }

    private function dayBounds(string $from, string $to): array
    {
        return [$from . ' 00:00:00', $to . ' 23:59:59'];
    }

    // ───── Overview ─────

    public function overviewKpis(string $from, string $to): array
    {
        [$f, $t] = $this->dayBounds($from, $to);
        $db = $this->db();

        $rev   = (float) ($db->table('payments')->selectSum('amount')
            ->where('paid_at >=', $f)->where('paid_at <=', $t)->where('status','success')
            ->get()->getRow('amount') ?? 0);
        $inv   = (int) $db->table('invoices')
            ->where('created_at >=', $f)->where('created_at <=', $t)
            ->countAllResults();
        $appts = (int) $db->table('appointments')
            ->where('start_at >=', $f)->where('start_at <=', $t)
            ->countAllResults();
        $newCust = (int) $db->table('customers')
            ->where('created_at >=', $f)->where('created_at <=', $t)
            ->countAllResults();
        $noShows = (int) $db->table('appointment_cancellations')
            ->where('cancelled_at >=', $f)->where('cancelled_at <=', $t)
            ->where('type','no_show')
            ->countAllResults();
        $cancels = (int) $db->table('appointment_cancellations')
            ->where('cancelled_at >=', $f)->where('cancelled_at <=', $t)
            ->where('type','cancelled')
            ->countAllResults();
        $avg = $inv > 0 ? $rev / $inv : 0;

        return compact('rev', 'inv', 'appts', 'newCust', 'noShows', 'cancels', 'avg');
    }

    /** Daily revenue series for the range. Returns [['date'=>'YYYY-MM-DD','revenue'=>123.45], ...] */
    public function dailyRevenue(string $from, string $to): array
    {
        [$f, $t] = $this->dayBounds($from, $to);
        $rows = $this->db()->query("
            SELECT DATE(paid_at) AS d, COALESCE(SUM(amount), 0) AS rev
            FROM payments
            WHERE status = 'success' AND paid_at BETWEEN ? AND ?
            GROUP BY DATE(paid_at)
            ORDER BY d ASC
        ", [$f, $t])->getResultArray();

        // Fill missing days with 0
        $byDay = [];
        foreach ($rows as $r) $byDay[$r['d']] = (float) $r['rev'];
        $out = [];
        $cur = strtotime($from); $end = strtotime($to);
        while ($cur <= $end) {
            $d = date('Y-m-d', $cur);
            $out[] = ['date' => $d, 'revenue' => $byDay[$d] ?? 0.0];
            $cur = strtotime('+1 day', $cur);
        }
        return $out;
    }

    // ───── Sales ─────

    public function paymentMethodBreakdown(string $from, string $to): array
    {
        [$f, $t] = $this->dayBounds($from, $to);
        return $this->db()->query("
            SELECT method, COUNT(*) AS n, COALESCE(SUM(amount), 0) AS total
            FROM payments
            WHERE status = 'success' AND paid_at BETWEEN ? AND ?
            GROUP BY method
            ORDER BY total DESC
        ", [$f, $t])->getResultArray();
    }

    public function invoiceTotals(string $from, string $to): array
    {
        [$f, $t] = $this->dayBounds($from, $to);
        $row = $this->db()->query("
            SELECT
              COALESCE(SUM(total),    0) AS billed,
              COALESCE(SUM(paid),     0) AS collected,
              COALESCE(SUM(balance),  0) AS outstanding,
              COUNT(*) AS invoices_n
            FROM invoices
            WHERE created_at BETWEEN ? AND ?
        ", [$f, $t])->getRowArray();
        return [
            'billed'      => (float) $row['billed'],
            'collected'   => (float) $row['collected'],
            'outstanding' => (float) $row['outstanding'],
            'invoices_n'  => (int)   $row['invoices_n'],
        ];
    }

    // ───── Services ─────

    /** Top services by booking count + revenue (within range, only completed/paid invoices) */
    public function topServices(string $from, string $to, int $limit = 50): array
    {
        [$f, $t] = $this->dayBounds($from, $to);
        // From appointment_services (joined on appointments within range)
        return $this->db()->query("
            SELECT s.name AS service,
                   COUNT(asv.id) AS bookings,
                   COALESCE(SUM(asv.price), 0) AS revenue,
                   COALESCE(SUM(asv.duration_min), 0) AS total_minutes
            FROM appointment_services asv
            JOIN appointments a ON a.id = asv.appointment_id
            LEFT JOIN services s ON s.id = asv.service_id
            WHERE a.start_at BETWEEN ? AND ?
              AND a.status IN ('confirmed','checked_in','in_progress','completed')
            GROUP BY asv.service_id, s.name
            ORDER BY bookings DESC, revenue DESC
            LIMIT ?
        ", [$f, $t, $limit])->getResultArray();
    }

    // ───── Staff performance ─────

    public function staffPerformance(string $from, string $to): array
    {
        [$f, $t] = $this->dayBounds($from, $to);
        // Revenue is driven by ACTUAL recorded payments (the source of truth), attributed to the
        // stylist on the invoice — falling back to the linked appointment's stylist when the invoice
        // has no explicit staff_id. Appointment counts still come from the appointments table (by
        // start date). This makes "record payment" reflect in the report immediately, instead of
        // relying on the booking-time subtotal estimate.
        return $this->db()->query("
            SELECT s.id, s.full_name, s.role, s.commission_pct,
                   COALESCE(ap.appointments_n, 0) AS appointments_n,
                   COALESCE(ap.completed_n, 0)    AS completed_n,
                   COALESCE(ap.no_shows_n, 0)     AS no_shows_n,
                   COALESCE(pm.revenue, 0)        AS revenue,
                   COALESCE(pm.revenue, 0)        AS completed_revenue
            FROM staff s
            LEFT JOIN (
                SELECT a.staff_id,
                       COUNT(*)                    AS appointments_n,
                       SUM(a.status = 'completed') AS completed_n,
                       SUM(a.status = 'no_show')   AS no_shows_n
                FROM appointments a
                WHERE a.start_at BETWEEN ? AND ?
                GROUP BY a.staff_id
            ) ap ON ap.staff_id = s.id
            LEFT JOIN (
                SELECT COALESCE(i.staff_id, ia.staff_id) AS staff_id,
                       SUM(p.amount) AS revenue
                FROM payments p
                JOIN invoices i           ON i.id  = p.invoice_id
                LEFT JOIN appointments ia ON ia.id = i.appointment_id
                WHERE p.status = 'success'
                  AND p.paid_at BETWEEN ? AND ?
                GROUP BY COALESCE(i.staff_id, ia.staff_id)
            ) pm ON pm.staff_id = s.id
            WHERE s.is_active = 1
            ORDER BY revenue DESC, appointments_n DESC
        ", [$f, $t, $f, $t])->getResultArray();
    }

    // ───── CSV helper ─────

    public static function toCsv(array $rows, array $headers): string
    {
        $h = fopen('php://temp', 'r+');
        fputcsv($h, $headers);
        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $col) $line[] = $row[$col] ?? '';
            fputcsv($h, $line);
        }
        rewind($h);
        return stream_get_contents($h);
    }
}
