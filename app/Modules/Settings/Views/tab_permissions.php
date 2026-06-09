<?php
$db = db_connect();
$roles = $db->table('roles')->orderBy('id')->get()->getResultArray();
$perms = $db->table('permissions')->orderBy('module')->orderBy('name')->get()->getResultArray();
$granted = [];
foreach ($db->table('role_permissions')->get()->getResultArray() as $g) {
    $granted[(int)$g['role_id']][(int)$g['permission_id']] = true;
}
// Group permissions by module
$byModule = [];
foreach ($perms as $p) $byModule[$p['module']][] = $p;
?>
<form method="POST" action="<?= site_url('admin/settings/permissions') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Permission matrix</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tick which roles can perform each action. <strong>super_admin</strong> always has all permissions and cannot be modified.</p>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save changes
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
            <thead class="bg-gray-50 dark:bg-white/5 sticky top-0">
                <tr>
                    <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Permission</th>
                    <?php foreach ($roles as $r): ?>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 min-w-[110px]">
                            <div class="font-semibold"><?= esc($r['label']) ?></div>
                            <code class="block text-[10px] text-gray-400 dark:text-gray-500 font-normal mt-0.5"><?= esc($r['name']) ?></code>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php foreach ($byModule as $module => $modulePerms): ?>
                    <tr class="bg-gray-50 dark:bg-white/5">
                        <td colspan="<?= 1 + count($roles) ?>" class="sticky left-0 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-white/5">
                            <?= esc($module) ?>
                        </td>
                    </tr>
                    <?php foreach ($modulePerms as $p): ?>
                        <tr class="hover:bg-brand-50/30 dark:hover:bg-brand-500/5">
                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-4 py-2">
                                <div class="font-medium text-gray-900 dark:text-white"><?= esc($p['label']) ?></div>
                                <code class="text-[10px] text-gray-400 dark:text-gray-500"><?= esc($p['name']) ?></code>
                            </td>
                            <?php foreach ($roles as $r):
                                $isSuper = $r['name'] === 'super_admin';
                                $checked = $isSuper || !empty($granted[$r['id']][$p['id']]);
                            ?>
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox"
                                           <?= $isSuper ? 'disabled checked' : '' ?>
                                           name="matrix[<?= (int)$r['id'] ?>][<?= (int)$p['id'] ?>]"
                                           value="1"
                                           <?= $checked ? 'checked' : '' ?>
                                           class="size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 disabled:opacity-50">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-end gap-3">
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save permission matrix
        </button>
    </div>
</form>
