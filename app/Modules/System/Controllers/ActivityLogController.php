<?php

namespace App\Modules\System\Controllers;

use App\Controllers\BaseController;
use App\Modules\System\Models\SystemLogModel;
use App\Modules\Auth\Models\UserModel;

class ActivityLogController extends BaseController
{
    public function index()
    {
        $action   = $this->request->getGet('action')   ?: null;
        $entity   = $this->request->getGet('entity')   ?: null;
        $userId   = (int) $this->request->getGet('user_id') ?: null;
        $severity = $this->request->getGet('severity') ?: null;
        $q        = $this->request->getGet('q')        ?: null;

        $model = new SystemLogModel();
        $rows  = $model->withFilters($action, $entity, $userId, $severity, $q, 300);

        $actions  = $model->distinctActions();
        $entities = $model->distinctEntities();
        $users    = (new UserModel())->orderBy('name')->findAll();

        return view('layout/admin', [
            'title'   => 'Activity log',
            'content' => view('App\Modules\System\Views\activity_log_index', compact('rows','actions','entities','users','action','entity','userId','severity','q')),
        ]);
    }
}
