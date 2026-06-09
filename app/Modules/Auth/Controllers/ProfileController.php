<?php

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Models\UserModel;

class ProfileController extends BaseController
{
    public function index()
    {
        $user = (new UserModel())->find((int) session('user.id'));
        if (! $user) return redirect()->to('/login');
        return view('layout/admin', [
            'title'   => 'My Profile',
            'content' => view('App\Modules\Auth\Views\profile', ['user' => $user]),
        ]);
    }

    public function update()
    {
        $id    = (int) session('user.id');
        $users = new UserModel();
        $user  = $users->find($id);
        if (! $user) return redirect()->to('/login');

        $rules = [
            'name'  => 'required|max_length[120]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('flash_error', implode(' · ', $this->validator->getErrors()));
        }

        $users->update($id, [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
        ]);

        // Keep session in sync so the topbar reflects the new name/email immediately.
        $sess = session('user');
        $sess['name']  = $this->request->getPost('name');
        $sess['email'] = $this->request->getPost('email');
        session()->set('user', $sess);

        helper('system');
        log_action('profile.update', ['entity_type' => 'user', 'entity_id' => $id, 'description' => 'Updated own profile']);

        return redirect()->to('/admin/profile')->with('flash_success', 'Profile updated.');
    }

    public function changePassword()
    {
        $id    = (int) session('user.id');
        $users = new UserModel();
        $user  = $users->find($id);
        if (! $user) return redirect()->to('/login');

        $current = (string) $this->request->getPost('current_password');
        $new     = (string) $this->request->getPost('new_password');
        $confirm = (string) $this->request->getPost('new_password_confirm');

        if (! password_verify($current, $user['password_hash'])) {
            return redirect()->to('/admin/profile')->with('flash_error', 'Your current password is incorrect.');
        }
        if (strlen($new) < 6)   return redirect()->to('/admin/profile')->with('flash_error', 'New password must be 6+ characters.');
        if ($new !== $confirm)  return redirect()->to('/admin/profile')->with('flash_error', 'New passwords do not match.');

        $users->update($id, ['password_hash' => password_hash($new, PASSWORD_BCRYPT)]);

        helper('system');
        log_action('password.change', ['entity_type' => 'user', 'entity_id' => $id, 'description' => 'Changed own password', 'severity' => 'warning']);

        return redirect()->to('/admin/profile')->with('flash_success', 'Password changed.');
    }
}
