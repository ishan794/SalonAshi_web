<?php

namespace App\Modules\Settings\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class PermissionsController extends BaseController
{
    public function save(): RedirectResponse
    {
        $matrix = $this->request->getPost('matrix') ?? [];
        // matrix[role_id][permission_id] = '1'  (only ticked boxes are submitted)

        $db = db_connect();
        $roleIds = array_map('intval', array_keys($matrix));

        // Always include all roles, even if no box was ticked (they'd be missing from $matrix)
        $allRoles = $db->table('roles')->select('id, name')->get()->getResultArray();

        foreach ($allRoles as $r) {
            $roleId = (int) $r['id'];
            // Never touch super_admin — always all
            if ($r['name'] === 'super_admin') continue;

            $db->table('role_permissions')->where('role_id', $roleId)->delete();
            $perms = array_keys($matrix[$roleId] ?? []);
            if (! $perms) continue;
            $rows = array_map(fn($pid) => ['role_id' => $roleId, 'permission_id' => (int)$pid], $perms);
            $db->table('role_permissions')->insertBatch($rows);
        }
        return redirect()->to('/admin/settings/permissions')->with('flash_success', 'Permissions saved.');
    }
}
