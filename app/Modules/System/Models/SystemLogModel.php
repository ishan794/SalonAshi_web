<?php

namespace App\Modules\System\Models;

use CodeIgniter\Model;

class SystemLogModel extends Model
{
    protected $table         = 'system_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id','user_name','action','entity_type','entity_id','description','ip_address','user_agent','payload_json','severity','created_at'];

    public function withFilters(?string $action = null, ?string $entity = null, ?int $userId = null, ?string $severity = null, ?string $q = null, int $limit = 200): array
    {
        $b = $this->orderBy('id', 'DESC');
        if ($action)   $b->like('action', $action);
        if ($entity)   $b->where('entity_type', $entity);
        if ($userId)   $b->where('user_id', $userId);
        if ($severity) $b->where('severity', $severity);
        if ($q)        $b->like('description', $q);
        return $b->findAll($limit);
    }

    public function distinctActions(): array
    {
        $rows = $this->select('action')->distinct()->orderBy('action')->findAll(200);
        return array_column($rows, 'action');
    }

    public function distinctEntities(): array
    {
        $rows = $this->select('entity_type')->distinct()->orderBy('entity_type')->where('entity_type IS NOT NULL', null, false)->findAll(50);
        return array_column($rows, 'entity_type');
    }
}
