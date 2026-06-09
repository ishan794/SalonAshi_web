<?php

namespace App\Modules\Api\Controllers;

use App\Modules\Services\Models\ServiceModel;

class ServicesController extends ApiBaseController
{
    /**
     * GET /api/services?active=1
     * Returns all services grouped with their category.
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $model    = new ServiceModel();
        $onlyActive = $this->request->getGet('active') !== '0'; // default: active only
        $services = $onlyActive ? $model->activeWithCategory() : $model->orderBy('name')->findAll();
        return $this->ok($services);
    }
}
