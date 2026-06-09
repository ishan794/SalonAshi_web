<?php

namespace App\Modules\System\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id','type','title','body','link','icon','color','is_read','read_at','created_at'];

    /** Notifications for a given user (or broadcast). */
    public function forUser(int $userId, ?bool $onlyUnread = null, int $limit = 50): array
    {
        $q = $this->groupStart()
                    ->where('user_id', $userId)
                    ->orWhere('user_id IS NULL', null, false)
                  ->groupEnd()
                  ->orderBy('created_at', 'DESC');
        if ($onlyUnread === true)  $q->where('is_read', 0);
        if ($onlyUnread === false) $q->where('is_read', 1);
        return $q->findAll($limit);
    }

    public function unreadCountFor(int $userId): int
    {
        return (int) $this->groupStart()
            ->where('user_id', $userId)
            ->orWhere('user_id IS NULL', null, false)
            ->groupEnd()
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function markRead(int $id, int $userId): void
    {
        $row = $this->find($id);
        if (! $row) return;
        if ($row['user_id'] && (int)$row['user_id'] !== $userId) return; // not yours
        $this->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    public function markAllRead(int $userId): int
    {
        return $this->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id IS NULL', null, false)
              ->groupEnd()
              ->where('is_read', 0)
              ->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])
              ->update();
    }
}
