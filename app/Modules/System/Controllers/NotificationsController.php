<?php

namespace App\Modules\System\Controllers;

use App\Controllers\BaseController;
use App\Modules\System\Models\NotificationModel;

class NotificationsController extends BaseController
{
    public function index()
    {
        $uid = (int) session('user.id');
        $filter = $this->request->getGet('filter') ?: 'all';
        $onlyUnread = $filter === 'unread' ? true : ($filter === 'read' ? false : null);
        $rows = (new NotificationModel())->forUser($uid, $onlyUnread, 200);

        return view('layout/admin', [
            'title'   => 'Notifications',
            'content' => view('App\Modules\System\Views\notifications_index', ['rows' => $rows, 'filter' => $filter]),
        ]);
    }

    public function markRead(int $id)
    {
        $uid = (int) session('user.id');
        (new NotificationModel())->markRead($id, $uid);
        return redirect()->back();
    }

    public function markAllRead()
    {
        $uid = (int) session('user.id');
        $n = (new NotificationModel())->markAllRead($uid);
        return redirect()->back()->with('flash_success', "{$n} notification(s) marked as read.");
    }

    public function destroy(int $id)
    {
        (new NotificationModel())->delete($id);
        return redirect()->back()->with('flash_success', 'Notification deleted.');
    }

    /** JSON for the topbar bell — recent + unread count. */
    public function topbarFeed()
    {
        $uid = (int) session('user.id');
        $m   = new NotificationModel();
        return $this->response->setJSON([
            'unread' => $m->unreadCountFor($uid),
            'items'  => $m->forUser($uid, null, 10),
        ]);
    }
}
