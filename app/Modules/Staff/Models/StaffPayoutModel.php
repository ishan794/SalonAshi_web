<?php

namespace App\Modules\Staff\Models;

use CodeIgniter\Model;

class StaffPayoutModel extends Model
{
    protected $table         = 'staff_payouts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'staff_id','period_from','period_to','gross_revenue','commission_pct','amount',
        'method','reference','slip_path','notes','status','notified_at','paid_at','created_by',
    ];

    /** All payouts for a stylist, newest first. */
    public function forStaff(int $staffId): array
    {
        return $this->where('staff_id', $staffId)->orderBy('id', 'DESC')->findAll();
    }
}
