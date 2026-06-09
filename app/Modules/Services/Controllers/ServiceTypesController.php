<?php

namespace App\Modules\Services\Controllers;

use App\Controllers\BaseController;
use App\Modules\Services\Models\ServiceTypeModel;

class ServiceTypesController extends BaseController
{
    public function index()
    {
        $rows = (new ServiceTypeModel())->orderBy('sort_order')->findAll();
        return view('layout/admin', [
            'title'   => 'Service Types',
            'content' => view('App\Modules\Services\Views\service_types_index', ['rows' => $rows]),
        ]);
    }

    public function store()
    {
        $in    = $this->request->getPost();
        $model = new ServiceTypeModel();
        $slug  = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim((string) $in['name'])));
        $model->insert([
            'name'        => trim((string) $in['name']),
            'slug'        => $slug,
            'color'       => $in['color'] ?: 'gray',
            'multiplier'  => (float) ($in['multiplier'] ?: 1),
            'description' => $in['description'] ?? '',
            'is_default'  => ! empty($in['is_default']) ? 1 : 0,
            'is_active'   => 1,
            'sort_order'  => (int) ($in['sort_order'] ?? 99),
        ]);
        if (! empty($in['is_default'])) {
            $model->where('id !=', (int) $model->getInsertID())->set(['is_default' => 0])->update();
        }
        return redirect()->to('/admin/service-types')->with('flash_success', 'Service type added.');
    }

    public function update(int $id)
    {
        $in    = $this->request->getRawInput();
        $model = new ServiceTypeModel();
        $model->update($id, [
            'name'        => trim((string) $in['name']),
            'color'       => $in['color'] ?: 'gray',
            'multiplier'  => (float) ($in['multiplier'] ?: 1),
            'description' => $in['description'] ?? '',
            'is_default'  => ! empty($in['is_default']) ? 1 : 0,
            'is_active'   => ! empty($in['is_active']) ? 1 : 0,
            'sort_order'  => (int) ($in['sort_order'] ?? 99),
        ]);
        if (! empty($in['is_default'])) {
            $model->where('id !=', $id)->set(['is_default' => 0])->update();
        }
        return redirect()->to('/admin/service-types')->with('flash_success', 'Service type updated.');
    }

    public function destroy(int $id)
    {
        (new ServiceTypeModel())->delete($id);
        return redirect()->to('/admin/service-types')->with('flash_success', 'Service type deleted.');
    }
}
