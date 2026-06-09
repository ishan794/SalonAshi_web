<?php

namespace App\Modules\Settings\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class UsersController extends BaseController
{
    public function store(): RedirectResponse
    {
        $rules = [
            'name'     => 'required|max_length[120]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role_id'  => 'required|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('flash_error', 'Check the form: ' . implode(' · ', $this->validator->getErrors()));
        }
        (new UserModel())->insert([
            'name'          => $this->request->getPost('name'),
            'email'         => $this->request->getPost('email'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role_id'       => (int) $this->request->getPost('role_id'),
            'branch_id'     => $this->request->getPost('branch_id') ?: null,
            'phone'         => $this->request->getPost('phone'),
            'status'        => 'active',
        ]);
        return redirect()->to('/admin/settings/users')->with('flash_success', 'User created.');
    }

    public function update(int $id): RedirectResponse
    {
        $rules = [
            'name'    => 'required|max_length[120]',
            'email'   => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role_id' => 'required|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('flash_error', implode(' · ', $this->validator->getErrors()));
        }
        $data = [
            'name'      => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'role_id'   => (int) $this->request->getPost('role_id'),
            'branch_id' => $this->request->getPost('branch_id') ?: null,
            'phone'     => $this->request->getPost('phone'),
            'status'    => $this->request->getPost('status') ?: 'active',
        ];
        $newPass = (string) $this->request->getPost('password');
        if ($newPass !== '') {
            if (strlen($newPass) < 6) {
                return redirect()->back()->with('flash_error', 'Password must be 6+ characters.');
            }
            $data['password_hash'] = password_hash($newPass, PASSWORD_BCRYPT);
        }
        (new UserModel())->update($id, $data);
        return redirect()->to('/admin/settings/users')->with('flash_success', 'User updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        if ((int) session('user.id') === $id) {
            return redirect()->back()->with('flash_error', 'You cannot delete your own account.');
        }
        (new UserModel())->delete($id);
        return redirect()->to('/admin/settings/users')->with('flash_success', 'User removed.');
    }
}
