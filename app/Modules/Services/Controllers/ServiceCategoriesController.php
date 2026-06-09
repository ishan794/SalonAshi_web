<?php

namespace App\Modules\Services\Controllers;

use App\Controllers\BaseController;
use App\Modules\Services\Models\ServiceCategoryModel;

class ServiceCategoriesController extends BaseController
{
    private ServiceCategoryModel $m;

    public function __construct()
    {
        $this->m = new ServiceCategoryModel();
    }

    public function index()
    {
        return view('layout/admin', [
            'title'   => 'Service Categories',
            'content' => view('App\Modules\Services\Views\categories_index', [
                'rows' => $this->m->orderBy('sort_order')->findAll(),
            ]),
        ]);
    }

    public function store()
    {
        if (! $this->validate(['name' => 'required|max_length[120]'])) {
            return redirect()->back()->withInput()->with('flash_error', 'Name required.');
        }
        $this->m->insert([
            'name'       => $this->request->getPost('name'),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => 1,
        ]);
        return redirect()->to('/admin/service-categories')->with('flash_success', 'Category added.');
    }

    public function destroy(int $id)
    {
        $this->m->delete($id);
        return redirect()->to('/admin/service-categories')->with('flash_success', 'Category removed.');
    }
}
