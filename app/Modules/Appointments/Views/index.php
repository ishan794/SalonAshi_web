<?php
$statusColors = [
    'pending' => 'amber','confirmed' => 'blue','checked_in' => 'cyan',
    'in_progress' => 'indigo','completed' => 'green','cancelled' => 'gray','no_show' => 'red',
];
?>
<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Appointments</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage bookings, calendar & status.</p>
        </div>
        <a href="<?= site_url('admin/appointments/create') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="plus" class="size-4"></i> New Appointment
        </a>
    </div>

    <!-- ── Summary cards for the selected range (IMP_008) ── -->
    <?php if (isset($stats)):
        $sc = [
            ['Total', $stats['total'], 'calendar-days', 'brand'],
            ['Completed', $stats['completed'], 'check-circle-2', 'green'],
            ['Pending', $stats['pending'], 'clock', 'amber'],
            ['Revenue', 'LKR ' . number_format((float)$stats['revenue'], 0), 'banknote', 'blue'],
        ];
    ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php foreach ($sc as [$label, $val, $icon, $col]): ?>
            <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4 flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-lg bg-<?= $col ?>-100 dark:bg-<?= $col ?>-500/20 text-<?= $col ?>-600 dark:text-<?= $col ?>-300 shrink-0">
                    <i data-lucide="<?= $icon ?>" class="size-4"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400"><?= esc($label) ?></p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white truncate"><?= esc($val) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="GET" class="flex flex-wrap items-end gap-3 bg-white dark:bg-gray-800 p-4 rounded-lg shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Date</label>
            <input type="date" name="date" value="<?= esc($date) ?>" class="mt-1 rounded-md border-gray-300 dark:border-white/10 text-sm focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 dark:bg-white/5 dark:text-white">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">View</label>
            <select name="view" class="mt-1 rounded-md border-gray-300 dark:border-white/10 text-sm focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 dark:bg-white/5 dark:text-white">
                <option value="day" <?= $view==='day'?'selected':'' ?>>Day</option>
                <option value="week" <?= $view==='week'?'selected':'' ?>>Week</option>
            </select>
        </div>
        <button class="rounded-md bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700">Apply</button>
        <span class="text-xs text-gray-500 dark:text-gray-400 ml-auto">Showing <?= esc($rangeStart) ?> → <?= esc($rangeEnd) ?> (<?= count($rows) ?> bookings)</span>
    </form>

    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Code</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Time</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Customer</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Stylist</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Status</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No appointments in this range.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $a):
                    $c = $statusColors[$a['status']] ?? 'gray';
                ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($a['code']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300"><?= esc(date('M j, H:i', strtotime($a['start_at']))) ?> → <?= esc(date('H:i', strtotime($a['end_at']))) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white"><?= esc($a['customer_name'] ?: '—') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300"><?= esc($a['staff_name'] ?: '—') ?></td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 px-2 py-0.5 text-xs font-medium text-<?= $c ?>-700 dark:text-<?= $c ?>-300"><?= esc(str_replace('_',' ', $a['status'])) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">LKR <?= number_format((float)$a['subtotal'], 2) ?></td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="<?= site_url('admin/appointments/'.$a['id']) ?>" class="text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300">Open</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
