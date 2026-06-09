<?php

namespace App\Modules\Customers\Models;

use CodeIgniter\Model;

class CustomerNoteModel extends Model
{
    protected $table         = 'customer_notes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['customer_id','staff_id','staff_name','note_type','title','body','is_pinned'];

    public function forCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('is_pinned', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
