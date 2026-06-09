<?php

namespace App\Modules\Api\Controllers;

class NotificationsController extends ApiBaseController
{
    /**
     * GET /api/notifications?unread=1&limit=50
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $uid     = $this->apiUser->id();
        $onlyUnread = (bool) $this->request->getGet('unread');
        $limit   = max(1, min(200, (int) ($this->request->getGet('limit') ?: 50)));

        $db = db_connect();
        $b  = $db->table('notifications')
            ->groupStart()
                ->where('user_id', $uid)
                ->orWhere('user_id IS NULL', null, false)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->limit($limit);

        if ($onlyUnread) $b->where('is_read', 0);

        $rows  = $b->get()->getResultArray();
        $unreadCount = (int) $db->table('notifications')
            ->groupStart()
                ->where('user_id', $uid)
                ->orWhere('user_id IS NULL', null, false)
            ->groupEnd()
            ->where('is_read', 0)->countAllResults();

        return $this->ok($rows, ['unread_count' => $unreadCount]);
    }

    /**
     * PATCH /api/notifications/{id}/read
     */
    public function markRead(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $uid = $this->apiUser->id();
        $db  = db_connect();
        $n   = $db->table('notifications')->where('id', $id)->get()->getRowArray();
        if (! $n) return $this->notFound('Notification not found.');
        if ($n['user_id'] && (int) $n['user_id'] !== $uid) return $this->forbidden();

        $db->table('notifications')->where('id', $id)->update(['is_read' => 1]);
        return $this->ok(['id' => $id, 'is_read' => true]);
    }

    /**
     * PATCH /api/notifications/read-all
     */
    public function markAllRead(): \CodeIgniter\HTTP\ResponseInterface
    {
        $uid = $this->apiUser->id();
        $db  = db_connect();
        $db->table('notifications')
            ->groupStart()->where('user_id', $uid)->orWhere('user_id IS NULL')->groupEnd()
            ->update(['is_read' => 1]);
        return $this->ok(null);
    }
}
