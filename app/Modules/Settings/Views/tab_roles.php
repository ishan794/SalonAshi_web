<?php
$db = db_connect();
$roles = $db->query("
    SELECT r.*,
           (SELECT COUNT(*) FROM users WHERE role_id = r.id) AS user_count,
           (SELECT COUNT(*) FROM role_permissions WHERE role_id = r.id) AS perm_count
    FROM roles r
    ORDER BY r.id
")->getResultArray();
?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Roles</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Each user is assigned exactly one role. Permissions are attached to roles on the <a href="<?= site_url('admin/settings/permissions') ?>" class="font-medium text-brand-600 dark:text-brand-400 hover:underline">Permissions</a> tab.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Roles list -->
        <div class="lg:col-span-2 rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Role</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Users</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Permissions</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php foreach ($roles as $r): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5"
                            x-data="{ edit: false, label: <?= json_encode($r['label']) ?> }">
                            <td class="px-4 py-3 text-sm">
                                <div x-show="!edit">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="label"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><code class="font-mono"><?= esc($r['name']) ?></code></p>
                                </div>
                                <form x-show="edit" method="POST" action="<?= site_url('admin/settings/roles/'.$r['id']) ?>" class="flex gap-1">
                                    <?= csrf_field() ?><input type="hidden" name="_method" value="PUT">
                                    <input name="label" x-model="label" class="flex-1 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                                    <button class="rounded-md bg-brand-600 px-2 py-1 text-xs font-semibold text-white"><i data-lucide="check" class="size-3.5"></i></button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300"><?= (int)$r['user_count'] ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300"><?= (int)$r['perm_count'] ?></td>
                            <td class="px-4 py-3 text-right text-sm">
                                <button type="button" @click="edit = !edit" class="text-brand-600 dark:text-brand-400 hover:text-brand-700" x-text="edit ? 'Cancel' : 'Edit'"></button>
                                <?php if ($r['name'] !== 'super_admin'): ?>
                                    <form method="POST" action="<?= site_url('admin/settings/roles/'.$r['id']) ?>" class="inline" onsubmit="return confirm('Delete this role?');">
                                        <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                        <button class="ml-3 text-red-600 dark:text-red-400 hover:text-red-700">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- New role -->
        <div class="rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 p-6">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Add role</h4>
            <form method="POST" action="<?= site_url('admin/settings/roles') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <?= view('components/form/input',['name'=>'label','label'=>'Display label','required'=>true,'placeholder'=>'Senior Stylist']) ?>
                <?= view('components/form/input',['name'=>'name','label'=>'Slug (system name)','required'=>true,'placeholder'=>'senior_stylist','helpText'=>'lowercase, no spaces — used internally']) ?>
                <button class="w-full rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">Create role</button>
            </form>
        </div>
    </div>
</div>
