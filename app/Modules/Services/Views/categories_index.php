<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-white/10"><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Categories</h3></div>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Name</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Sort</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php foreach ($rows as $r): ?>
                    <tr><td class="px-4 py-2.5 text-sm font-medium text-gray-900 dark:text-white"><?= esc($r['name']) ?></td>
                        <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400"><?= (int)$r['sort_order'] ?></td>
                        <td class="px-4 py-2.5 text-right text-sm">
                            <form method="POST" action="<?= site_url('admin/service-categories/'.$r['id']) ?>" class="inline" onsubmit="return confirm('Delete?');">
                                <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:text-red-300">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Add category</h3>
        <form method="POST" action="<?= site_url('admin/service-categories') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <?= view('components/form/input',['name'=>'name','label'=>'Name','required'=>true]) ?>
            <?= view('components/form/input',['name'=>'sort_order','label'=>'Sort order','type'=>'number','value'=>'0']) ?>
            <button class="w-full rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add</button>
        </form>
    </div>
</div>
