<?php

namespace App\Modules\Settings\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class RolesController extends BaseController
{
    private function tbl() { return db_connect()->table('roles'); }

    public function store(): RedirectResponse
    {
        if (! $this->validate(['name' => 'required|alpha_dash|max_length[50]|is_unique[roles.name]', 'label' => 'required|max_length[100]'])) {
            return redirect()->back()->withInput()->with('flash_error', implode(' · ', $this->validator->getErrors()));
        }
        $this->tbl()->insert([
            'name'       => $this->request->getPost('name'),
            'label'      => $this->request->getPost('label'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/settings/roles')->with('flash_success', 'Role added.');
    }

    public function update(int $id): RedirectResponse
    {
        if (! $this->validate(['label' => 'required|max_length[100]'])) {
            return redirect()->back()->withInput()->with('flash_error', implode(' · ', $this->validator->getErrors()));
        }
        // Don't allow renaming the system 'name' field — only label
        $this->tbl()->where('id', $id)->update(['label' => $this->request->getPost('label')]);
        return redirect()->to('/admin/settings/roles')->with('flash_success', 'Role updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $r = $this->tbl()->where('id', $id)->get()->getRowArray();
        if (! $r) return redirect()->back()->with('flash_error', 'Role not found.');
        if ($r['name'] === 'super_admin') {
            return redirect()->back()->with('flash_error', 'super_admin is a protected system role.');
        }
        $inUse = (int) db_connect()->table('users')->where('role_id', $id)->countAllResults();
        if ($inUse > 0) {
            return redirect()->back()->with('flash_error', "Role has $inUse user(s) assigned — reassign them first.");
        }
        db_connect()->table('role_permissions')->where('role_id', $id)->delete();
        $this->tbl()->where('id', $id)->delete();
        return redirect()->to('/admin/settings/roles')->with('flash_success', 'Role deleted.');
    }
}
