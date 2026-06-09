<?php

namespace App\Modules\Services\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table         = 'services';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['category_id','name','description','duration_min','price','tax_pct','is_active'];

    public function withCategory(): array
    {
        return $this->db->table('services s')
            ->select('s.*, c.name AS category_name')
            ->join('service_categories c', 'c.id = s.category_id', 'left')
            ->orderBy('s.id', 'desc')
            ->get()->getResultArray();
    }

    public function activeWithCategory(): array
    {
        return $this->db->table('services s')
            ->select('s.*, c.name AS category_name')
            ->join('service_categories c', 'c.id = s.category_id', 'left')
            ->where('s.is_active', 1)
            ->orderBy('c.name', 'asc')
            ->orderBy('s.name', 'asc')
            ->get()->getResultArray();
    }
}
