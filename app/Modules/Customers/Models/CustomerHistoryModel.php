<?php

namespace App\Modules\Customers\Models;

use CodeIgniter\Model;

class CustomerHistoryModel extends Model
{
    protected $table         = 'customer_service_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'customer_id', 'appointment_id', 'service_id', 'service_name',
        'staff_id', 'staff_name', 'branch_id', 'service_date', 'duration_min', 'price',
        'notes', 'product_used', 'formula', 'before_image', 'after_image',
        'rating', 'invoice_id', 'payment_status',
    ];

    public function forCustomer(int $customerId, int $limit = 100): array
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('service_date', 'DESC')
            ->findAll($limit);
    }

    /** Backfill history rows from a completed appointment. Idempotent — won't duplicate. */
    public function recordFromAppointment(array $appt): int
    {
        if (! $appt || empty($appt['id'])) return 0;
        $existing = (int) $this->where('appointment_id', (int) $appt['id'])->countAllResults();
        if ($existing > 0) return 0;

        $services = db_connect()->table('appointment_services')
            ->where('appointment_id', (int) $appt['id'])
            ->get()->getResultArray();
        $count = 0;
        foreach ($services as $sv) {
            $this->insert([
                'customer_id'    => (int) $appt['customer_id'],
                'appointment_id' => (int) $appt['id'],
                'service_id'     => (int) $sv['service_id'],
                'service_name'   => $sv['service_name'] ?? '—',
                'staff_id'       => (int) $appt['staff_id'],
                'staff_name'     => $appt['staff_name'] ?? null,
                'branch_id'      => (int) ($appt['branch_id'] ?? 0) ?: null,
                'service_date'   => $appt['start_at'],
                'duration_min'   => (int) $sv['duration_min'],
                'price'          => (float) $sv['price'],
            ]);
            $count++;
        }
        return $count;
    }
}
