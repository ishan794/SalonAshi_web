<?php

namespace App\Modules\Api\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Modules\Api\Models\ApiTokenModel;

/**
 * Validates Bearer tokens on all /api/* routes.
 * Whitelists: POST api/auth/login, and all OPTIONS preflight requests.
 * On success: populates service('apiUser') with the authenticated user's data.
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Allow OPTIONS preflight (handled by CORS filter)
        if (strtolower($request->getMethod()) === 'options') {
            return;
        }

        // Whitelist the login endpoint — no token needed
        $uri = trim(service('uri')->getPath(), '/');
        if ($uri === 'api/auth/login') {
            return;
        }

        // Extract Bearer token from Authorization header
        $header = $request->getHeaderLine('Authorization');
        if (! str_starts_with($header, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'msg' => 'Missing Authorization header. Please log in.']);
        }

        $rawToken   = substr($header, 7);
        $tokenModel = new ApiTokenModel();
        $record     = $tokenModel->findValidToken($rawToken);

        if (! $record) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'msg' => 'Token invalid or expired. Please log in again.']);
        }

        // Slide expiry (30 days from now)
        $tokenModel->slideExpiry((int) $record['id']);

        // Load user + role + branch from DB
        $db   = db_connect();
        $user = $db->table('users u')
            ->select('u.*, r.name AS role_name, r.label AS role_label, b.name AS branch_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->where('u.id', (int) $record['user_id'])
            ->get()->getRowArray();

        if (! $user || $user['status'] !== 'active') {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'msg' => 'Account is inactive or deleted.']);
        }

        // Load permissions for this role
        $perms = array_column(
            $db->table('role_permissions rp')
                ->select('p.name')
                ->join('permissions p', 'p.id = rp.permission_id')
                ->where('rp.role_id', (int) $user['role_id'])
                ->get()->getResultArray(),
            'name'
        );

        // Look up linked staff record (stylists have user_id set on their staff row)
        $staffRow = $db->table('staff')
            ->select('id, full_name, commission_pct')
            ->where('user_id', (int) $user['id'])
            ->where('is_active', 1)
            ->get()->getRowArray();

        service('apiUser')->set([
            'id'          => (int) $user['id'],
            'name'        => $user['name'],
            'email'       => $user['email'],
            'role'        => $user['role_name'],
            'role_label'  => $user['role_label'],
            'branch_id'   => $user['branch_id'] ? (int) $user['branch_id'] : null,
            'branch_name' => $user['branch_name'] ?? null,
            'perms'       => $perms,
            'staff_id'    => $staffRow ? (int) $staffRow['id'] : null,
            'staff_name'  => $staffRow ? $staffRow['full_name'] : null,
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void {}
}
