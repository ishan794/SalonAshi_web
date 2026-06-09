<?php
/** @var array $rows; @var string $from, $to, $preset; @var float $totalRevenue, $totalCommission */
$presets = ['today' => 'Today', '7d' => '7 days', '30d' => '30 days', 'mtd' => 'This month', 'ytd' => 'This year'];
?>
<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Payouts</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Commission due to each stylist, based on actual recorded payments.</p>
        </div>
    </div>

    <!-- Range selector -->
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-3">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mr-1">Period:</span>
            <?php foreach ($presets as $k => $label): ?>
                <a href="?preset=<?= $k ?>" class="rounded-md px-2.5 py-1 text-xs font-semibold <?= $preset === $k ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
        <span class="text-xs text-gray-500 dark:text-gray-400"><?= esc(date('M j', strtotime($from))) ?> — <?= esc(date('M j, Y', strtotime($to))) ?></span>
    </div>

    <!-- KPI cards -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4 flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300 shrink-0"><i data-lucide="banknote" class="size-4"></i></span>
            <div><p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Gross revenue</p><p class="text-lg font-bold text-gray-900 dark:text-white">LKR <?= number_format($totalRevenue, 0) ?></p></div>
        </div>
        <div class="rounded-lg bg-gradient-to-br from-brand-500 to-amber-600 p-4 text-white shadow-lg shadow-brand-500/20 flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-lg bg-white/20 text-white shrink-0"><i data-lucide="hand-coins" class="size-4"></i></span>
            <div><p class="text-xs uppercase tracking-wide font-semibold text-white/80">Total payout due</p><p class="text-lg font-bold">LKR <?= number_format($totalCommission, 0) ?></p></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4 flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 shrink-0"><i data-lucide="users" class="size-4"></i></span>
            <div><p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Stylists</p><p class="text-lg font-bold text-gray-900 dark:text-white"><?= count($rows) ?></p></div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold">Stylist</th>
                        <th class="px-4 py-2.5 text-right font-semibold">Completed</th>
                        <th class="px-4 py-2.5 text-right font-semibold">Revenue</th>
                        <th class="px-4 py-2.5 text-right font-semibold">Rate</th>
                        <th class="px-4 py-2.5 text-right font-semibold">Payout due</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No active stylists.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="size-8 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 text-white flex items-center justify-center text-xs font-semibold shrink-0"><?= esc(strtoupper(substr($r['full_name'], 0, 1))) ?></span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= esc($r['full_name']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($r['role'] ?: 'Stylist') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300"><?= (int) $r['completed_n'] ?></td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">LKR <?= number_format((float)$r['revenue'], 0) ?></td>
                            <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400"><?= number_format((float)$r['commission_pct'], 1) ?>%</td>
                            <td class="px-4 py-3 text-right font-semibold text-brand-600 dark:text-brand-400">LKR <?= number_format((float)$r['commission'], 0) ?></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="<?= site_url('admin/reports/staff/'.(int)$r['id'].'/payout?preset='.esc($preset).'&from='.esc($from).'&to='.esc($to)) ?>" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline"><i data-lucide="file-text" class="size-3.5"></i> PDF</a>
                                <a href="<?= site_url('admin/staff/'.(int)$r['id'].'/payouts') ?>" class="ml-3 text-xs text-gray-500 dark:text-gray-400 hover:underline">Details →</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (! empty($rows)): ?>
                    <tfoot class="bg-gray-50 dark:bg-white/5 font-semibold">
                        <tr>
                            <td class="px-4 py-2.5 text-xs uppercase text-gray-600 dark:text-gray-400">Totals</td>
                            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300"><?= array_sum(array_column($rows, 'completed_n')) ?></td>
                            <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">LKR <?= number_format($totalRevenue, 0) ?></td>
                            <td></td>
                            <td class="px-4 py-2.5 text-right text-brand-600 dark:text-brand-400">LKR <?= number_format($totalCommission, 0) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
