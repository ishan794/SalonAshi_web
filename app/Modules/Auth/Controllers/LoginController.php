<?php

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Models\UserModel;

class LoginController extends BaseController
{
    public function index()
    {
        return view('layout/auth', [
            'title'   => 'Sign in — SalonCMS',
            'content' => view('App\Modules\Auth\Views\login', [], ['saveData' => true]),
        ]);
    }

    public function attempt()
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if ($email === '' || $password === '') {
            return redirect()->back()->withInput()->with('flash_error', 'Please enter email and password.');
        }

        $users = new UserModel();
        $user  = $users->findByEmail($email);

        if (! $user || $user['status'] !== 'active' || ! password_verify($password, $user['password_hash'])) {
            helper('system');
            log_action('login.failed', ['description' => 'Failed login attempt for ' . $email, 'severity' => 'warning']);
            return redirect()->back()->withInput()->with('flash_error', 'Invalid credentials.');
        }

        $full = $users->withRole((int) $user['id']);

        // Load all permission slugs granted to this role
        $perms = array_column(
            db_connect()->table('role_permissions rp')
                ->select('p.name')
                ->join('permissions p', 'p.id = rp.permission_id')
                ->where('rp.role_id', (int) $full['role_id'])
                ->get()->getResultArray(),
            'name'
        );

        session()->set('user', [
            'id'          => (int) $full['id'],
            'name'        => $full['name'],
            'email'       => $full['email'],
            'role'        => $full['role_name'],
            'role_label'  => $full['role_label'],
            'branch_id'   => $full['branch_id'] ? (int) $full['branch_id'] : null,
            'branch_name' => $full['branch_name'],
            'perms'       => $perms,
        ]);

        $users->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        helper('system');
        log_action('login.success', ['entity_type' => 'user', 'entity_id' => (int) $user['id'], 'description' => $full['name'] . ' signed in']);

        return redirect()->to('/admin/dashboard');
    }

    public function logout()
    {
        $name = session('user.name'); $uid = (int) session('user.id');
        helper('system');
        if ($uid) log_action('logout', ['entity_type' => 'user', 'entity_id' => $uid, 'description' => $name . ' signed out']);
        session()->destroy();
        return redirect()->to('/login')->with('flash_success', 'Signed out.');
    }
}
