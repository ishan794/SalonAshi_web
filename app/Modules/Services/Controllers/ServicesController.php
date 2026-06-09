<?php

namespace App\Modules\Services\Controllers;

use App\Controllers\BaseController;
use App\Modules\Services\Models\ServiceModel;
use App\Modules\Services\Models\ServiceCategoryModel;

class ServicesController extends BaseController
{
    private ServiceModel $services;
    private ServiceCategoryModel $categories;

    public function __construct()
    {
        $this->services   = new ServiceModel();
        $this->categories = new ServiceCategoryModel();
    }

    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));

        $query = $this->services->select('services.*, service_categories.name AS category_name')
             ->join('service_categories', 'service_categories.id = services.category_id', 'left');

        if ($q !== '') {
            $query->groupStart()
                  ->like('services.name', $q)
                  ->orLike('service_categories.name', $q)
                  ->orLike('services.description', $q)
                  ->groupEnd();
        }

        $rows = $query->orderBy('services.id', 'desc')->paginate(10);

        return view('layout/admin', [
            'title'   => 'Services',
            'content' => view('App\Modules\Services\Views\index', [
                'rows'  => $rows,
                'pager' => $this->services->pager,
                'q'     => $q,
            ]),
        ]);
    }

    public function create()
    {
        return view('layout/admin', [
            'title'   => 'New Service',
            'content' => view('App\Modules\Services\Views\form', [
                'row' => null,
                'categories' => $this->categories->orderBy('sort_order')->findAll(),
            ]),
        ]);
    }

    public function store()
    {
        $data = $this->validatedInput();
        if (! $data) return redirect()->back()->withInput();
        $this->services->insert($data);
        return redirect()->to('/admin/services')->with('flash_success', 'Service added.');
    }

    public function edit(int $id)
    {
        $row = $this->services->find($id);
        if (! $row) return redirect()->to('/admin/services');
        return view('layout/admin', [
            'title'   => 'Edit Service',
            'content' => view('App\Modules\Services\Views\form', [
                'row' => $row,
                'categories' => $this->categories->orderBy('sort_order')->findAll(),
            ]),
        ]);
    }

    public function update(int $id)
    {
        $data = $this->validatedInput();
        if (! $data) return redirect()->back()->withInput();
        $this->services->update($id, $data);
        return redirect()->to('/admin/services')->with('flash_success', 'Service updated.');
    }

    public function destroy(int $id)
    {
        $this->services->delete($id);
        return redirect()->to('/admin/services')->with('flash_success', 'Service deleted.');
    }

    private function validatedInput(): ?array
    {
        $rules = [
            'name'         => 'required|max_length[150]',
            'duration_min' => 'required|integer|greater_than[0]',
            'price'        => 'required|decimal',
        ];
        if (! $this->validate($rules)) {
            session()->setFlashdata('flash_error', 'Please fix the errors below.');
            return null;
        }
        $in = $this->request->getPost();
        return [
            'category_id'  => $in['category_id'] ?: null,
            'name'         => $in['name'],
            'description'  => $in['description'] ?? null,
            'duration_min' => (int) $in['duration_min'],
            'price'        => (float) $in['price'],
            'tax_pct'      => isset($in['tax_pct']) ? (float) $in['tax_pct'] : 0,
            'is_active'    => !empty($in['is_active']) ? 1 : 0,
        ];
    }
}
