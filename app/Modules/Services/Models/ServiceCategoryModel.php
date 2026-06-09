<?php

namespace App\Modules\Services\Models;

use CodeIgniter\Model;

class ServiceCategoryModel extends Model
{
    protected $table         = 'service_categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name','sort_order','is_active'];
}
