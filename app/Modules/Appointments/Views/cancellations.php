<?php /** @var array $recent, $offenders, $totals */ ?>
<div class="space-y-6">
    <div class="flex items-end justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Cancellations &amp; no-shows</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track who's bailing on appointments without notice or payment.</p>
        </div>
        <a href="<?= site_url('admin/appointments') ?>" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white inline-flex items-center gap-1">
            <i data-lucide="arrow-left" class="size-4"></i> Back to appointments
        </a>
    </div>

    <!-- KPI tiles -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-xs"><i data-lucide="ban" class="size-4"></i> Cancellations</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?= (int) $totals['cancellations'] ?></div>
            <p class="text-xs text-gray-500 dark:text-gray-400"><?= (int) $totals['cancel_late'] ?> with &lt; 24 h notice</p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-red-600 dark:text-red-400 text-xs"><i data-lucide="user-x" class="size-4"></i> No-shows</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?= (int) $totals['no_shows'] ?></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">customers who didn't turn up</p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-green-600 dark:text-green-400 text-xs"><i data-lucide="banknote" class="size-4"></i> Fees collected</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">LKR <?= number_format((float) $totals['fees_collected'], 0) ?></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">total cancellation fees</p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 text-xs"><i data-lucide="users" class="size-4"></i> Flagged customers</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?= count($offenders) ?></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">at least one no-show / late cancel</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top offenders -->
        <div class="lg:col-span-1 rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top offenders</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Ranked by no-shows + late cancellations</p>
            </div>
            <?php if (empty($offenders)): ?>
                <p class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No problem customers yet. 🎉</p>
            <?php else: ?>
                <ul class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php foreach ($offenders as $o): $score = (int)$o['no_shows'] * 2 + (int)$o['cancel_late']; ?>
                        <li class="px-5 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                            <a href="<?= site_url('admin/customers/'.(int)$o['id']) ?>" class="block">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= esc($o['full_name']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc(phone_local($o['mobile'])) ?></p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-500/20 px-2 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300"><?= $score ?> pts</span>
                                </div>
                                <div class="mt-1.5 flex flex-wrap gap-1.5 text-[10px]">
                                    <?php if ((int)$o['no_shows']): ?>
                                        <span class="inline-flex items-center gap-1 rounded bg-red-50 dark:bg-red-500/15 px-1.5 py-0.5 text-red-700 dark:text-red-300"><?= (int)$o['no_shows'] ?> no-show<?= (int)$o['no_shows'] === 1 ? '' : 's' ?></span>
                                    <?php endif; ?>
                                    <?php if ((int)$o['cancel_late']): ?>
                                        <span class="inline-flex items-center gap-1 rounded bg-amber-50 dark:bg-amber-500/15 px-1.5 py-0.5 text-amber-700 dark:text-amber-300"><?= (int)$o['cancel_late'] ?> late cancel</span>
                                    <?php endif; ?>
                                    <?php if ((int)$o['cancel_ok']): ?>
                                        <span class="inline-flex items-center gap-1 rounded bg-gray-100 dark:bg-white/10 px-1.5 py-0.5 text-gray-600 dark:text-gray-300"><?= (int)$o['cancel_ok'] ?> w/ notice</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Recent log -->
        <div class="lg:col-span-2 rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent activity</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Last 50 events</p>
            </div>
            <?php if (empty($recent)): ?>
                <p class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No cancellations yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">When</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Customer</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Notice</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">By</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Fee</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php foreach ($recent as $r):
                                $hrs = (float)$r['notice_hours'];
                                $isLate = $hrs < 24;
                                $noticeTxt = $r['type'] === 'no_show'
                                    ? ($hrs >= 0 ? 'mark at +0' : '+' . number_format(abs($hrs), 1) . ' h late')
                                    : number_format(max($hrs, 0), 1) . ' h before';
                            ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap"><?= esc(date('M j, H:i', strtotime($r['cancelled_at']))) ?></td>
                                    <td class="px-3 py-2"><a href="<?= site_url('admin/customers/'.(int)$r['customer_id']) ?>" class="text-sm font-medium text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-300"><?= esc($r['customer_name']) ?></a><div class="text-[11px] text-gray-500 dark:text-gray-400"><?= esc($r['appt_code']) ?></div></td>
                                    <td class="px-3 py-2"><?php if ($r['type'] === 'no_show'): ?><span class="inline-flex rounded-full bg-red-100 dark:bg-red-500/20 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-300">no-show</span><?php else: ?><span class="inline-flex rounded-full bg-amber-100 dark:bg-amber-500/20 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">cancelled</span><?php endif; ?></td>
                                    <td class="px-3 py-2 text-xs whitespace-nowrap <?= $isLate ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-600 dark:text-gray-400' ?>"><?= $noticeTxt ?></td>
                                    <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400 capitalize"><?= esc($r['cancelled_by']) ?></td>
                                    <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 whitespace-nowrap"><?= (float)$r['fee_charged'] > 0 ? 'LKR ' . number_format((float)$r['fee_charged'], 0) : '—' ?></td>
                                    <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400 max-w-[280px] truncate" title="<?= esc($r['reason']) ?>"><?= esc($r['reason'] ?: '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
