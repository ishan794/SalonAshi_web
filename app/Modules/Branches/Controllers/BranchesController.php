<?php

namespace App\Modules\Branches\Controllers;

use App\Controllers\BaseController;

class BranchesController extends BaseController
{
    public function index()
    {
        $rows = db_connect()->table('branches')->orderBy('id','desc')->get()->getResultArray();
        return view('layout/admin', [
            'title'   => 'Branches',
            'content' => view('App\Modules\Branches\Views\index', ['rows' => $rows]),
        ]);
    }

    public function store()
    {
        if (! $this->validate(['name' => 'required|max_length[120]'])) {
            return redirect()->back()->withInput()->with('flash_error', 'Branch name required.');
        }
        db_connect()->table('branches')->insert([
            'name' => $this->request->getPost('name'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/branches')->with('flash_success', 'Branch added.');
    }

    public function destroy(int $id)
    {
        db_connect()->table('branches')->where('id', $id)->delete();
        return redirect()->to('/admin/branches')->with('flash_success', 'Branch removed.');
    }
}
