<?php /** @var array $rows, $actions, $entities, $users; @var ?string $action, $entity, $severity, $q; @var ?int $userId */
$sevColors = ['info' => 'gray', 'warning' => 'amber', 'error' => 'red'];
?>
<div class="space-y-4">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Activity log</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Audit trail of significant actions — logins, bookings, payments, settings changes, errors.</p>
    </div>

    <form method="GET" class="rounded-lg bg-white dark:bg-gray-800 p-4 ring-1 ring-gray-200 dark:ring-white/10 grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Action</label>
            <select name="action" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                <option value="">All</option>
                <?php foreach ($actions as $a): ?><option value="<?= esc($a) ?>" <?= $action===$a?'selected':'' ?>><?= esc($a) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Entity</label>
            <select name="entity" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                <option value="">All</option>
                <?php foreach ($entities as $e): ?><option value="<?= esc($e) ?>" <?= $entity===$e?'selected':'' ?>><?= esc($e) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">User</label>
            <select name="user_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                <option value="">All</option>
                <?php foreach ($users as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (int)$userId === (int)$u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Severity</label>
            <select name="severity" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                <option value="">All</option>
                <option value="info"    <?= $severity==='info'?'selected':'' ?>>info</option>
                <option value="warning" <?= $severity==='warning'?'selected':'' ?>>warning</option>
                <option value="error"   <?= $severity==='error'?'selected':'' ?>>error</option>
            </select>
        </div>
        <div class="sm:col-span-1 col-span-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Search</label>
            <input name="q" value="<?= esc($q ?? '') ?>" placeholder="description text" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
        </div>
        <div class="sm:col-span-5 flex items-center justify-end gap-2">
            <a href="<?= site_url('admin/activity-log') ?>" class="rounded-md bg-gray-100 dark:bg-white/5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">Reset</a>
            <button class="rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">Apply filters</button>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg ring-1 ring-gray-200 dark:ring-white/10">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold">When</th>
                    <th class="px-3 py-2 text-left font-semibold">User</th>
                    <th class="px-3 py-2 text-left font-semibold">Action</th>
                    <th class="px-3 py-2 text-left font-semibold">Entity</th>
                    <th class="px-3 py-2 text-left font-semibold">Description</th>
                    <th class="px-3 py-2 text-left font-semibold">Severity</th>
                    <th class="px-3 py-2 text-left font-semibold">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/10 bg-white dark:bg-gray-900">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No log entries match.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): $sc = $sevColors[$r['severity']] ?? 'gray'; ?>
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400"><?= esc(date('M j, Y H:i:s', strtotime($r['created_at']))) ?></td>
                        <td class="px-3 py-2 text-xs"><?= esc($r['user_name'] ?: '—') ?></td>
                        <td class="px-3 py-2 text-xs"><code class="font-mono"><?= esc($r['action']) ?></code></td>
                        <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400"><?= $r['entity_type'] ? esc($r['entity_type']) . ($r['entity_id'] ? ' #' . (int)$r['entity_id'] : '') : '—' ?></td>
                        <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300"><?= esc($r['description']) ?></td>
                        <td class="px-3 py-2"><span class="inline-flex items-center rounded-full bg-<?= $sc ?>-100 dark:bg-<?= $sc ?>-500/20 text-<?= $sc ?>-700 dark:text-<?= $sc ?>-300 px-2 py-0.5 text-[10px] font-semibold uppercase"><?= esc($r['severity']) ?></span></td>
                        <td class="px-3 py-2 text-[11px] font-mono text-gray-500 dark:text-gray-400"><?= esc($r['ip_address'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400">Showing up to 300 most recent entries.</p>
</div>
