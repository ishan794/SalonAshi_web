<?php

namespace App\Modules\Customers\Models;

use CodeIgniter\Model;

class CustomerFileModel extends Model
{
    protected $table         = 'customer_files';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['customer_id','file_name','file_path','mime_type','size_bytes','label','uploaded_by','created_at'];

    public function forCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)->orderBy('created_at', 'DESC')->findAll();
    }
}
