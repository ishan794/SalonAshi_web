<?php /** @var array $rows */ ?>
<div class="space-y-4" x-data="{ adding: false, editingId: null }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Service Types</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tiers like Standard, Emergency, VIP — each with its own price multiplier. Used at booking time.</p>
        </div>
        <button @click="adding = !adding" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="plus" class="size-4"></i> New type
        </button>
    </div>

    <!-- Add form -->
    <form x-show="adding" x-cloak method="POST" action="<?= site_url('admin/service-types') ?>" class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow ring-1 ring-gray-200 dark:ring-white/10 grid grid-cols-1 sm:grid-cols-6 gap-3">
        <?= csrf_field() ?>
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input name="name" required placeholder="e.g. Emergency" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Color</label>
            <select name="color" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                <option>gray</option><option>red</option><option>amber</option><option>green</option><option>blue</option><option>brand</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Multiplier</label>
            <input name="multiplier" type="number" step="0.05" min="0" value="1.00" required class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Sort</label>
            <input name="sort_order" type="number" value="99" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
        </div>
        <div class="sm:col-span-6">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Description</label>
            <input name="description" placeholder="Optional short description" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
        </div>
        <div class="sm:col-span-6 flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200"><input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500"> Set as default type</label>
            <div class="flex gap-2">
                <button type="button" @click="adding = false" class="rounded-md bg-gray-100 dark:bg-white/5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200">Cancel</button>
                <button class="rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">Save</button>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg ring-1 ring-gray-200 dark:ring-white/10">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold">Name</th>
                    <th class="px-4 py-2 text-left font-semibold">Slug</th>
                    <th class="px-4 py-2 text-left font-semibold">Color</th>
                    <th class="px-4 py-2 text-right font-semibold">Multiplier</th>
                    <th class="px-4 py-2 text-left font-semibold">Description</th>
                    <th class="px-4 py-2 text-center font-semibold">Default</th>
                    <th class="px-4 py-2 text-center font-semibold">Active</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/10 bg-white dark:bg-gray-900">
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="px-4 py-2"><span class="inline-flex items-center gap-1.5 rounded-full bg-<?= esc($r['color']) ?>-100 dark:bg-<?= esc($r['color']) ?>-500/20 text-<?= esc($r['color']) ?>-700 dark:text-<?= esc($r['color']) ?>-300 px-2.5 py-0.5 text-xs font-semibold"><?= esc($r['name']) ?></span></td>
                        <td class="px-4 py-2 text-xs font-mono text-gray-500 dark:text-gray-400"><?= esc($r['slug']) ?></td>
                        <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400"><?= esc($r['color']) ?></td>
                        <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-white"><?= number_format((float)$r['multiplier'], 2) ?>×</td>
                        <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-400"><?= esc($r['description']) ?></td>
                        <td class="px-4 py-2 text-center"><?= $r['is_default'] ? '<i data-lucide="check-circle-2" class="size-4 text-brand-500 inline"></i>' : '' ?></td>
                        <td class="px-4 py-2 text-center"><?= $r['is_active'] ? '<span class="inline-block size-2 rounded-full bg-green-500"></span>' : '<span class="inline-block size-2 rounded-full bg-gray-300"></span>' ?></td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="<?= site_url('admin/service-types/' . (int)$r['id']) ?>" class="inline" onsubmit="return confirm('Delete this type? Existing bookings keep their snapshot prices.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="text-xs font-semibold text-red-600 hover:text-red-700 dark:text-red-400">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
