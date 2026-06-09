<?php

namespace App\Modules\Staff\Models;

use CodeIgniter\Model;

class StaffModel extends Model
{
    protected $table         = 'staff';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'branch_id','user_id','full_name','role','mobile','email',
        'commission_pct','working_hours','is_active',
        // Payout & bank details
        'bank_name','bank_account_name','bank_account_no','bank_branch','bank_code',
        'payout_method','payout_frequency','payout_notes',
    ];

    public function servicesFor(int $staffId): array
    {
        return $this->db->table('staff_services ss')
            ->select('s.id, s.name')
            ->join('services s', 's.id = ss.service_id')
            ->where('ss.staff_id', $staffId)
            ->get()->getResultArray();
    }

    public function setServices(int $staffId, array $serviceIds): void
    {
        $this->db->table('staff_services')->where('staff_id', $staffId)->delete();
        $serviceIds = array_filter(array_map('intval', $serviceIds));
        if (! $serviceIds) return;
        $rows = array_map(fn($id) => ['staff_id'=>$staffId,'service_id'=>$id], $serviceIds);
        $this->db->table('staff_services')->insertBatch($rows);
    }

    // ───── Schedule (weekly working hours) ─────

    /**
     * Return the staff's weekly schedule keyed by day-of-week (0=Sun … 6=Sat).
     * Always returns 7 rows; missing rows default to "off" so the UI can render
     * a full week even on first edit.
     */
    public function getSchedule(int $staffId): array
    {
        $rows = $this->db->table('staff_schedule')->where('staff_id', $staffId)->get()->getResultArray();
        $byDow = [];
        foreach ($rows as $r) $byDow[(int)$r['dow']] = $r;
        $out = [];
        for ($d = 0; $d < 7; $d++) {
            $out[$d] = $byDow[$d] ?? [
                'staff_id'   => $staffId,
                'dow'        => $d,
                'start_time' => '09:00:00',
                'end_time'   => '18:00:00',
                'is_off'     => $d === 0 ? 1 : 0,  // sensible default: Sunday off
            ];
        }
        return $out;
    }

    /**
     * Replace the staff's weekly schedule.
     * $byDow expects format: [0 => ['start_time'=>'09:00','end_time'=>'18:00','is_off'=>0], ...]
     */
    public function saveSchedule(int $staffId, array $byDow): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('staff_schedule')->where('staff_id', $staffId)->delete();
        $rows = [];
        for ($d = 0; $d < 7; $d++) {
            $r = $byDow[$d] ?? [];
            $rows[] = [
                'staff_id'   => $staffId,
                'dow'        => $d,
                'start_time' => $r['start_time'] ?? '09:00:00',
                'end_time'   => $r['end_time']   ?? '18:00:00',
                'is_off'     => !empty($r['is_off']) ? 1 : 0,
                'updated_at' => $now,
            ];
        }
        $this->db->table('staff_schedule')->insertBatch($rows);
    }

    /**
     * Hours for a given date — kept for backwards compatibility.
     * Returns the FIRST window only (legacy single-tuple shape) or NULL if off.
     */
    public function hoursForDate(int $staffId, string $date): ?array
    {
        $windows = $this->windowsForDate($staffId, $date);
        return $windows === null ? null : $windows[0];
    }

    /**
     * Working windows for a given date. Returns array of [start, end] tuples
     * (HH:MM strings) or NULL if the staff is off that day.
     * Precedence:
     *   1. time-off table              → off (NULL)
     *   2. per-date custom windows     → those windows
     *   3. per-day-of-week schedule    → its single [start,end]
     *   4. legacy staff.working_hours  → its single [start,end]
     *   5. fallback default            → [['09:00','19:00']]
     */
    public function windowsForDate(int $staffId, string $date): ?array
    {
        // 1. Explicit time-off override
        $off = $this->db->table('staff_time_off')
            ->where('staff_id', $staffId)
            ->where('off_date', $date)
            ->countAllResults();
        if ($off > 0) return null;

        // 2. Per-date custom windows
        $dateWindows = $this->db->table('staff_date_windows')
            ->where('staff_id', $staffId)
            ->where('on_date', $date)
            ->orderBy('start_time', 'asc')
            ->get()->getResultArray();
        if (! empty($dateWindows)) {
            return array_map(
                fn($w) => [substr($w['start_time'], 0, 5), substr($w['end_time'], 0, 5)],
                $dateWindows
            );
        }

        // 3. Per-day-of-week schedule
        $dow = (int) date('w', strtotime($date));
        $row = $this->db->table('staff_schedule')
            ->where('staff_id', $staffId)
            ->where('dow', $dow)
            ->get()->getRowArray();
        if ($row) {
            if (! empty($row['is_off'])) return null;
            return [[substr($row['start_time'], 0, 5), substr($row['end_time'], 0, 5)]];
        }

        // 4. Legacy staff.working_hours
        $staff = $this->find($staffId);
        $wh = trim((string) ($staff['working_hours'] ?? ''));
        if (preg_match('/^(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})$/', $wh, $m)) {
            return [[$m[1], $m[2]]];
        }

        // 5. Fallback default
        return [['09:00', '19:00']];
    }

    // ───── Per-date custom windows ─────

    public function getDateWindows(int $staffId, string $date): array
    {
        return $this->db->table('staff_date_windows')
            ->where('staff_id', $staffId)
            ->where('on_date', $date)
            ->orderBy('start_time', 'asc')
            ->get()->getResultArray();
    }

    public function addDateWindow(int $staffId, string $date, string $start, string $end, ?string $note = null): bool
    {
        // Normalize HH:MM → HH:MM:SS
        $start = strlen($start) === 5 ? $start . ':00' : $start;
        $end   = strlen($end)   === 5 ? $end   . ':00' : $end;
        if ($start >= $end) return false;
        $this->db->table('staff_date_windows')->insert([
            'staff_id'   => $staffId,
            'on_date'    => $date,
            'start_time' => $start,
            'end_time'   => $end,
            'note'       => $note ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function removeDateWindow(int $id, int $staffId): void
    {
        $this->db->table('staff_date_windows')
            ->where('id', $id)
            ->where('staff_id', $staffId)
            ->delete();
    }

    public function clearDateWindows(int $staffId, string $date): void
    {
        $this->db->table('staff_date_windows')
            ->where('staff_id', $staffId)
            ->where('on_date', $date)
            ->delete();
    }

    // ───── Time-off ─────

    public function getTimeOff(int $staffId, ?string $from = null): array
    {
        $q = $this->db->table('staff_time_off')->where('staff_id', $staffId);
        if ($from) $q->where('off_date >=', $from);
        return $q->orderBy('off_date', 'asc')->get()->getResultArray();
    }

    public function addTimeOff(int $staffId, string $date, ?string $reason = null): bool
    {
        try {
            $this->db->table('staff_time_off')->insert([
                'staff_id'   => $staffId,
                'off_date'   => $date,
                'reason'     => $reason,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            return true;
        } catch (\Throwable $e) {
            return false; // probably duplicate (unique constraint)
        }
    }

    public function removeTimeOff(int $id, int $staffId): void
    {
        $this->db->table('staff_time_off')
            ->where('id', $id)
            ->where('staff_id', $staffId)
            ->delete();
    }
}
