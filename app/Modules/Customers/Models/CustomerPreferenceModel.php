<?php

namespace App\Modules\Customers\Models;

use CodeIgniter\Model;

class CustomerPreferenceModel extends Model
{
    protected $table         = 'customer_preferences';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['customer_id','preference_key','preference_value'];

    public function forCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)->orderBy('preference_key')->findAll();
    }

    public function setKv(int $customerId, string $key, string $value): void
    {
        $row = $this->where('customer_id', $customerId)->where('preference_key', $key)->first();
        if ($row) $this->update($row['id'], ['preference_value' => $value]);
        else      $this->insert(['customer_id' => $customerId, 'preference_key' => $key, 'preference_value' => $value]);
    }
}
