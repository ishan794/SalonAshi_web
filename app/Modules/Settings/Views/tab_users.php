<?php
$db = db_connect();
$users = $db->table('users u')
    ->select('u.*, r.label AS role_label, r.name AS role_name, b.name AS branch_name')
    ->join('roles r','r.id=u.role_id','left')
    ->join('branches b','b.id=u.branch_id','left')
    ->orderBy('u.id','desc')->get()->getResultArray();
$roles    = $db->table('roles')->orderBy('id')->get()->getResultArray();
$branches = $db->table('branches')->where('is_active',1)->orderBy('name')->get()->getResultArray();
?>
<div class="space-y-6"
     x-data="{ open:false, editing:null,
               openCreate(){ this.editing=null; this.open=true; },
               openEdit(u){ this.editing=u; this.open=true; } }">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Login users</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Anyone who signs in to SalonCMS. Each user has one role.</p>
        </div>
        <button type="button" @click="openCreate()" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="user-plus" class="size-4"></i> Add user
        </button>
    </div>

    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Name</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Email</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Role</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Branch</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($u['name']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($u['email']) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center rounded-full bg-brand-50 dark:bg-brand-500/15 px-2 py-0.5 text-xs font-medium text-brand-700 dark:text-brand-300"><?= esc($u['role_label']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($u['branch_name'] ?: '—') ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php if ($u['status']==='active'): ?>
                                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-500/20 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">Active</span>
                            <?php else: ?>
                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <button type="button" @click='openEdit(<?= json_encode($u) ?>)' class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">Edit</button>
                            <?php if ((int)$u['id'] !== (int)session('user.id')): ?>
                                <form method="POST" action="<?= site_url('admin/settings/users/'.$u['id']) ?>" class="inline" onsubmit="return confirm('Delete this user?');">
                                    <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                    <button class="ml-3 text-red-600 hover:text-red-700 dark:text-red-400">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal: create / edit -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="open=false">
        <div class="fixed inset-0 bg-gray-900/60 dark:bg-gray-900" @click="open=false"></div>
        <div class="relative w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-gray-200 dark:ring-white/10">
            <form :action="editing ? '<?= site_url('admin/settings/users') ?>/' + editing.id : '<?= site_url('admin/settings/users') ?>'"
                  method="POST" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit user' : 'New user'"></h3>
                    <button type="button" @click="open=false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"><i data-lucide="x" class="size-5"></i></button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Full name</label>
                        <input name="name" required :value="editing?.name||''" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input name="email" type="email" required :value="editing?.email||''" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                        <input name="phone" :value="editing?.phone||''" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Role</label>
                        <select name="role_id" required class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int)$r['id'] ?>" :selected="editing?.role_id == <?= (int)$r['id'] ?>"><?= esc($r['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                        <select name="branch_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">— None —</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= (int)$b['id'] ?>" :selected="editing?.branch_id == <?= (int)$b['id'] ?>"><?= esc($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div x-show="editing">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Status</label>
                        <select name="status" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="active" :selected="editing?.status==='active'">Active</option>
                            <option value="disabled" :selected="editing?.status==='disabled'">Disabled</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2" x-data="{ showPw: false }">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white" x-text="editing ? 'New password (leave blank to keep current)' : 'Password'"></label>
                        <div class="relative mt-1">
                            <!-- autocomplete=new-password stops Chrome silently autofilling (and thus resetting) the password on edit -->
                            <input name="password" :type="showPw ? 'text' : 'password'" :required="!editing" minlength="6"
                                   autocomplete="new-password"
                                   class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm pr-10 focus:border-brand-500 focus:ring-brand-500">
                            <button type="button" @click="showPw = !showPw" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                    :aria-label="showPw ? 'Hide password' : 'Show password'">
                                <i x-show="!showPw" data-lucide="eye" class="size-4"></i>
                                <i x-show="showPw" x-cloak data-lucide="eye-off" class="size-4"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="editing" x-cloak>Leave blank to keep the current password unchanged.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200 dark:border-white/10">
                    <button type="button" @click="open=false" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">Cancel</button>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700">
                        <i data-lucide="check" class="size-4"></i> <span x-text="editing ? 'Save changes' : 'Create user'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
