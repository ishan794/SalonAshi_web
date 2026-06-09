<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Branches</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr><th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Name</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Location / Address</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Phone</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Email</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php foreach ($rows as $r): ?>
                    <tr><td class="px-4 py-3 text-sm font-medium"><?= esc($r['name']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-xs"><?= $r['address'] ? nl2br(esc($r['address'])) : '—' ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($r['phone'] ?: '—') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($r['email'] ?: '—') ?></td>
                        <td class="px-4 py-3 text-right text-sm">
                            <form method="POST" action="<?= site_url('admin/branches/'.$r['id']) ?>" class="inline" onsubmit="return confirm('Delete?');">
                                <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:text-red-300">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 p-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Add branch</h3>
        <form method="POST" action="<?= site_url('admin/branches') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <?= view('components/form/input',['name'=>'name','label'=>'Name','required'=>true]) ?>
            <?= view('components/form/textarea',['name'=>'address','label'=>'Address','rows'=>2]) ?>
            <?= view('components/form/input',['name'=>'phone','label'=>'Phone']) ?>
            <?= view('components/form/input',['name'=>'email','label'=>'Email','type'=>'email']) ?>
            <button class="w-full rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add</button>
        </form>
    </div>
</div>
