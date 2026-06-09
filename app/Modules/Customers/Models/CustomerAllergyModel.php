<?php

namespace App\Modules\Customers\Models;

use CodeIgniter\Model;

class CustomerAllergyModel extends Model
{
    protected $table         = 'customer_allergies';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['customer_id','allergy_name','severity','notes'];

    public function forCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('FIELD(severity, "severe","moderate","mild")', '', false)
            ->findAll();
    }
}
