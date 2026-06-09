<?php

namespace App\Modules\Api\Controllers;

use App\Modules\Api\Models\ApiTokenModel;
use App\Modules\Auth\Models\UserModel;

class AuthController extends ApiBaseController
{
    /**
     * POST /api/auth/login
     * Body: { email, password, device_name? }
     * Returns: { ok, data: { token, user: { id,name,email,role,permissions[],staff_id? } } }
     */
    public function login(): \CodeIgniter\HTTP\ResponseInterface
    {
        $body     = $this->body();
        $email    = trim((string) ($body['email']       ?? ''));
        $password = (string) ($body['password']    ?? '');
        $device   = trim((string) ($body['device_name'] ?? ''));

        if ($email === '' || $password === '') {
            return $this->fail('email and password are required.', 400);
        }

        $users = new UserModel();
        $user  = $users->findByEmail($email);

        if (! $user || $user['status'] !== 'active' || ! password_verify($password, $user['password_hash'])) {
            return $this->fail('Invalid credentials.', 401);
        }

        $full = $users->withRole((int) $user['id']);
        $db   = db_connect();

        $perms = array_column(
            $db->table('role_permissions rp')
                ->select('p.name')
                ->join('permissions p', 'p.id = rp.permission_id')
                ->where('rp.role_id', (int) $full['role_id'])
                ->get()->getResultArray(),
            'name'
        );

        // Linked staff record (for stylists)
        $staffRow = $db->table('staff')
            ->select('id, full_name, commission_pct')
            ->where('user_id', (int) $user['id'])
            ->where('is_active', 1)
            ->get()->getRowArray();

        $tokenModel = new ApiTokenModel();
        $rawToken   = $tokenModel->issue((int) $user['id'], $device ?: null);

        $users->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return $this->ok([
            'token' => $rawToken,
            'user'  => [
                'id'          => (int) $full['id'],
                'name'        => $full['name'],
                'email'       => $full['email'],
                'role'        => $full['role_name'],
                'role_label'  => $full['role_label'],
                'branch_id'   => $full['branch_id'] ? (int) $full['branch_id'] : null,
                'branch_name' => $full['branch_name'] ?? null,
                'permissions' => $perms,
                'staff_id'    => $staffRow ? (int) $staffRow['id'] : null,
                'staff_name'  => $staffRow ? $staffRow['full_name'] : null,
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     * Revokes the current Bearer token only.
     */
    public function logout(): \CodeIgniter\HTTP\ResponseInterface
    {
        $raw = substr($this->request->getHeaderLine('Authorization'), 7);
        if ($raw) (new ApiTokenModel())->revoke($raw);
        return $this->ok(null);
    }

    /**
     * POST /api/auth/logout-all
     * Revokes ALL tokens for the authenticated user (sign out every device).
     */
    public function logoutAll(): \CodeIgniter\HTTP\ResponseInterface
    {
        (new ApiTokenModel())->revokeAllForUser($this->apiUser->id());
        return $this->ok(null);
    }
}
