<?php /** @var array $data */
$rows = $data['rows'];
$totalRev = array_sum(array_column($rows, 'revenue'));
$totalAppts = array_sum(array_column($rows, 'appointments_n'));
?>
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Staff performance</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400"><?= $totalAppts ?> appointment<?= $totalAppts === 1 ? '' : 's' ?> · LKR <?= number_format($totalRev, 0) ?> revenue across <?= count($rows) ?> staff</p>
        </div>
        <a href="<?= site_url('admin/reports/staff/csv?preset='.esc($preset).'&from='.esc($from).'&to='.esc($to)) ?>"
           class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="download" class="size-4"></i> CSV
        </a>
    </div>

    <?php if (empty($rows)): ?>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-10 text-center shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <i data-lucide="user-cog" class="mx-auto size-10 text-gray-300 dark:text-gray-600"></i>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No active staff yet.</p>
        </div>
    <?php else: ?>
        <div class="rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Staff</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Appts</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Completed</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">No-shows</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Revenue</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Comm %</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Commission</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php foreach ($rows as $r):
                            $rev = (float)$r['revenue'];
                            $share = $totalRev > 0 ? round(($rev / $totalRev) * 100, 1) : 0;
                            $noShowRate = (int)$r['appointments_n'] > 0 ? round(((int)$r['no_shows_n'] / (int)$r['appointments_n']) * 100, 0) : 0;
                        ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                                            <?= esc(strtoupper(substr($r['full_name'], 0, 1))) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= esc($r['full_name']) ?></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($r['role'] ?: 'Staff') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300"><?= (int)$r['appointments_n'] ?></td>
                                <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400"><?= (int)$r['completed_n'] ?></td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <?php if ((int)$r['no_shows_n'] > 0): ?>
                                        <span class="text-red-600 dark:text-red-400 font-medium"><?= (int)$r['no_shows_n'] ?></span>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400">(<?= $noShowRate ?>%)</span>
                                    <?php else: ?>
                                        <span class="text-gray-400 dark:text-gray-600">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">LKR <?= number_format($rev, 0) ?></td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400"><?= number_format((float)$r['commission_pct'], 1) ?>%</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-brand-600 dark:text-brand-400">LKR <?= number_format((float)$r['commission'], 0) ?></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="<?= site_url('admin/reports/staff/'.(int)$r['id'].'/payout?preset='.esc($preset).'&from='.esc($from).'&to='.esc($to)) ?>" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline" title="Download commission payout PDF">
                                        <i data-lucide="file-text" class="size-3.5"></i> Payout
                                    </a>
                                    <a href="<?= site_url('admin/staff/'.(int)$r['id'].'/calendar') ?>" class="ml-3 text-xs text-gray-600 dark:text-gray-400 hover:underline">Calendar →</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-white/5 font-semibold">
                        <tr>
                            <td class="px-4 py-2 text-xs uppercase text-gray-600 dark:text-gray-400">Totals</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white"><?= $totalAppts ?></td>
                            <td class="px-4 py-2 text-sm text-right text-green-600 dark:text-green-400"><?= array_sum(array_column($rows, 'completed_n')) ?></td>
                            <td class="px-4 py-2 text-sm text-right text-red-600 dark:text-red-400"><?= array_sum(array_column($rows, 'no_shows_n')) ?></td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">LKR <?= number_format($totalRev, 0) ?></td>
                            <td class="px-4 py-2"></td>
                            <td class="px-4 py-2 text-sm text-right text-brand-600 dark:text-brand-400">LKR <?= number_format(array_sum(array_column($rows, 'commission')), 0) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
