<?php

namespace App\Modules\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Route-level permission gate.
 * Usage: ['filter' => 'perm:invoices.delete']
 * Aborts with 403 if the current user lacks the named permission.
 * super_admin role always passes.
 */
class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('user.id')) {
            return redirect()->to('/login')->with('flash_error', 'Please sign in to continue.');
        }
        $role = session('user.role');
        if ($role === 'super_admin') return; // bypass

        $need = is_array($arguments) ? $arguments : (array) $arguments;
        if (empty($need)) return;

        $perms = session('user.perms') ?: [];
        foreach ($need as $perm) {
            if (in_array($perm, $perms, true)) return; // ANY-match passes
        }

        return service('response')
            ->setStatusCode(403)
            ->setBody(view('layout/admin', [
                'title'   => 'Permission denied',
                'content' => view('App\Modules\Auth\Views\forbidden', ['needed' => $need]),
            ]));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
