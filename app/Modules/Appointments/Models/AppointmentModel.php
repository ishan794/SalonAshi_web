<?php

namespace App\Modules\Appointments\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table         = 'appointments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'code','customer_id','staff_id','branch_id','start_at','end_at',
        'status','subtotal','notes','created_by',
    ];

    public function forRange(string $from, string $to): array
    {
        return $this->db->table('appointments a')
            ->select('a.*, c.full_name AS customer_name, s.full_name AS staff_name')
            ->join('customers c','c.id=a.customer_id','left')
            ->join('staff s','s.id=a.staff_id','left')
            ->where('a.start_at >=', $from)
            ->where('a.start_at <=', $to)
            ->orderBy('a.start_at','asc')
            ->get()->getResultArray();
    }

    public function withDetail(int $id): ?array
    {
        $row = $this->db->table('appointments a')
            ->select('a.*, c.full_name AS customer_name, c.mobile AS customer_mobile, c.email AS customer_email, s.full_name AS staff_name')
            ->join('customers c','c.id=a.customer_id','left')
            ->join('staff s','s.id=a.staff_id','left')
            ->where('a.id', $id)
            ->get()->getRowArray();
        if (! $row) return null;
        $row['services'] = $this->db->table('appointment_services')
            ->where('appointment_id', $id)->get()->getResultArray();
        return $row;
    }

    /** All appointments for a customer (newest first) — for linking to an invoice. */
    public function forCustomer(int $customerId): array
    {
        return $this->db->table('appointments a')
            ->select('a.id, a.code, a.start_at, a.status, a.staff_id, s.full_name AS staff_name')
            ->join('staff s','s.id=a.staff_id','left')
            ->where('a.customer_id', $customerId)
            ->orderBy('a.start_at','desc')
            ->get()->getResultArray();
    }

    public function conflicts(int $staffId, string $start, string $end, ?int $exceptId = null): bool
    {
        $q = $this->db->table('appointments')
            ->where('staff_id', $staffId)
            ->whereIn('status', ['pending','confirmed','checked_in','in_progress'])
            ->where('start_at <', $end)
            ->where('end_at >', $start);
        if ($exceptId) $q->where('id !=', $exceptId);
        return (int) $q->countAllResults() > 0;
    }
}
