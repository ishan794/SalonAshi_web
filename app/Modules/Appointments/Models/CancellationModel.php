<?php

namespace App\Modules\Appointments\Models;

use CodeIgniter\Model;

class CancellationModel extends Model
{
    protected $table         = 'appointment_cancellations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'appointment_id','customer_id','type','cancelled_by',
        'scheduled_at','cancelled_at','notice_hours','reason',
        'fee_charged','recorded_by','created_at',
    ];

    /**
     * Record a cancellation/no-show from the given appointment.
     * Computes notice_hours as (scheduled_at - cancelled_at) in hours.
     * Negative notice means the cancellation came AFTER the scheduled time (no-show).
     */
    public function record(array $appt, string $type, string $by, ?string $reason, float $fee, ?int $recordedBy): int
    {
        $now      = time();
        $sched    = strtotime((string) $appt['start_at']);
        $hours    = round(($sched - $now) / 3600, 2);

        return (int) $this->insert([
            'appointment_id' => (int) $appt['id'],
            'customer_id'    => (int) $appt['customer_id'],
            'type'           => $type === 'no_show' ? 'no_show' : 'cancelled',
            'cancelled_by'   => in_array($by, ['customer','staff','system'], true) ? $by : 'staff',
            'scheduled_at'   => $appt['start_at'],
            'cancelled_at'   => date('Y-m-d H:i:s'),
            'notice_hours'   => $hours,
            'reason'         => $reason ?: null,
            'fee_charged'    => $fee,
            'recorded_by'    => $recordedBy,
            'created_at'     => date('Y-m-d H:i:s'),
        ], true);
    }

    /**
     * Per-customer reliability stats.
     * Returns:
     *   total, completed, cancelled_with_notice, cancelled_late, no_shows, reliability ('good'|'watch'|'risk')
     */
    public function reliabilityFor(int $customerId): array
    {
        $db = db_connect();

        $appts = (int) $db->table('appointments')->where('customer_id', $customerId)->countAllResults();
        $completed = (int) $db->table('appointments')->where('customer_id', $customerId)->where('status', 'completed')->countAllResults();

        $cancellations = $db->table('appointment_cancellations')->where('customer_id', $customerId)->get()->getResultArray();
        $cancelLate = 0; $cancelOk = 0; $noShows = 0;
        foreach ($cancellations as $c) {
            if ($c['type'] === 'no_show') { $noShows++; continue; }
            $hrs = (float) $c['notice_hours'];
            if ($hrs < 24) $cancelLate++; else $cancelOk++;
        }

        $badEvents = $noShows + $cancelLate;
        $score = $appts > 0 ? max(0, 100 - round(($badEvents / $appts) * 100)) : 100;

        if ($badEvents === 0)                $level = 'good';
        elseif ($badEvents <= 1 || $score >= 75) $level = 'watch';
        else                                  $level = 'risk';

        return [
            'total_appts'       => $appts,
            'completed'         => $completed,
            'cancel_with_notice'=> $cancelOk,
            'cancel_late'       => $cancelLate,
            'no_shows'          => $noShows,
            'bad_events'        => $badEvents,
            'score'             => $score,
            'level'             => $level,
        ];
    }

    /** Top customers by no-show + late-cancel count */
    public function topOffenders(int $limit = 20): array
    {
        // NOTE: HAVING/ORDER BY repeat the aggregate expressions instead of using aliases —
        // MariaDB rejects alias references inside HAVING for aggregate functions.
        return db_connect()->query("
            SELECT c.id, c.full_name, c.mobile, c.email,
                   SUM(ac.type = 'no_show') AS no_shows,
                   SUM(ac.type = 'cancelled' AND ac.notice_hours < 24) AS cancel_late,
                   SUM(ac.type = 'cancelled' AND ac.notice_hours >= 24) AS cancel_ok,
                   SUM(ac.fee_charged) AS fees,
                   MAX(ac.cancelled_at) AS last_event
            FROM appointment_cancellations ac
            JOIN customers c ON c.id = ac.customer_id
            GROUP BY c.id
            HAVING (SUM(ac.type = 'no_show') + SUM(ac.type = 'cancelled' AND ac.notice_hours < 24)) > 0
            ORDER BY (SUM(ac.type = 'no_show') * 2 + SUM(ac.type = 'cancelled' AND ac.notice_hours < 24)) DESC,
                     MAX(ac.cancelled_at) DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    /** Recent cancellation log */
    public function recent(int $limit = 50): array
    {
        return db_connect()->query("
            SELECT ac.*, a.code AS appt_code, c.full_name AS customer_name, c.mobile AS customer_mobile
            FROM appointment_cancellations ac
            LEFT JOIN appointments a ON a.id = ac.appointment_id
            LEFT JOIN customers c    ON c.id = ac.customer_id
            ORDER BY ac.cancelled_at DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }
}
