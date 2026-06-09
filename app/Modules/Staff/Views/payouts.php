<?php
/** @var array $staff, $rows; @var string $from, $to; @var float $totalRevenue, $totalCommission, $pct */
$activeTab = 'payouts';
$presets = [
    'today' => 'Today',
    '7d'    => '7 days',
    '30d'   => '30 days',
    'mtd'   => 'This month',
    'ytd'   => 'This year',
];
$curPreset = $_GET['preset'] ?? '30d';
$pdfUrl = site_url('admin/reports/staff/' . (int)$staff['id'] . '/payout?preset=' . $curPreset . '&from=' . $from . '&to=' . $to);
?>
<?php include __DIR__ . '/_tabs.php'; ?>

<div class="space-y-4">
    <!-- Date range -->
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-3">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mr-1">Range:</span>
            <?php foreach ($presets as $k => $label): ?>
                <a href="?preset=<?= $k ?>" class="rounded-md px-2.5 py-1 text-xs font-semibold <?= $curPreset === $k ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
        <a href="<?= esc($pdfUrl) ?>" target="_blank" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
            <i data-lucide="file-text" class="size-3.5"></i> Download payout PDF
        </a>
    </div>

    <!-- Summary card -->
    <div class="rounded-lg bg-gradient-to-br from-brand-50 to-amber-50 dark:from-brand-500/10 dark:to-amber-500/10 ring-1 ring-brand-200 dark:ring-brand-500/30 p-5 grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Period</p>
            <p class="mt-0.5 text-sm font-bold text-gray-900 dark:text-white"><?= esc(date('M j', strtotime($from))) ?> — <?= esc(date('M j, Y', strtotime($to))) ?></p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Gross revenue</p>
            <p class="mt-0.5 text-lg font-bold text-gray-900 dark:text-white">LKR <?= number_format($totalRevenue, 2) ?></p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Commission rate</p>
            <p class="mt-0.5 text-lg font-bold text-gray-900 dark:text-white"><?= number_format($pct, 1) ?>%</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Payout due</p>
            <p class="mt-0.5 text-xl font-bold text-brand-600 dark:text-brand-400">LKR <?= number_format($totalCommission, 2) ?></p>
        </div>
    </div>

    <?php
    $inputCls = 'w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500';
    $lblCls   = 'block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1';
    $payoutMethods = ['' => '— Select —','bank_transfer'=>'Bank transfer','cash'=>'Cash','cheque'=>'Cheque','mobile_wallet'=>'Mobile wallet'];
    ?>

    <!-- Generate payout -->
    <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-5"
         x-data="{ open: <?= empty($payouts) ? 'true' : 'false' ?> }">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="flex size-8 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300"><i data-lucide="hand-coins" class="size-4"></i></span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Generate payout</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Record a payout, attach the bank slip, and notify the stylist.</p>
                </div>
            </div>
            <button type="button" @click="open = !open" class="inline-flex items-center gap-1 rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                <i data-lucide="plus" class="size-3.5"></i> <span x-text="open ? 'Close' : 'New payout'"></span>
            </button>
        </div>

        <form x-show="open" x-cloak x-transition method="POST" action="<?= site_url('admin/staff/'.(int)$staff['id'].'/payouts') ?>" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="gross_revenue" value="<?= esc(number_format($totalRevenue, 2, '.', '')) ?>">
            <div>
                <label class="<?= $lblCls ?>">Period from</label>
                <input type="date" name="period_from" value="<?= esc($from) ?>" class="<?= $inputCls ?>">
            </div>
            <div>
                <label class="<?= $lblCls ?>">Period to</label>
                <input type="date" name="period_to" value="<?= esc($to) ?>" class="<?= $inputCls ?>">
            </div>
            <div>
                <label class="<?= $lblCls ?>">Amount (LKR) <span class="text-gray-400">· due <?= number_format($totalCommission, 2) ?></span></label>
                <input type="number" step="0.01" min="0" name="amount" required value="<?= esc(number_format($totalCommission, 2, '.', '')) ?>" class="<?= $inputCls ?> font-semibold">
            </div>
            <div>
                <label class="<?= $lblCls ?>">Method</label>
                <select name="method" class="<?= $inputCls ?>">
                    <?php $dm = $staff['payout_method'] ?? ''; foreach ($payoutMethods as $v => $t): ?>
                        <option value="<?= esc($v) ?>" <?= $dm === $v ? 'selected' : '' ?>><?= esc($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="<?= $lblCls ?>">Reference (optional)</label>
                <input type="text" name="reference" placeholder="Txn / cheque no" class="<?= $inputCls ?>">
            </div>
            <div>
                <label class="<?= $lblCls ?>">Bank slip (optional)</label>
                <input type="file" name="slip" accept="image/*,application/pdf" class="block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700">
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="<?= $lblCls ?>">Notes (optional)</label>
                <input type="text" name="notes" placeholder="e.g. June commission, less LKR 2,000 advance" class="<?= $inputCls ?>">
            </div>
            <div class="sm:col-span-2 lg:col-span-3 flex items-center justify-between flex-wrap gap-3 pt-1">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="mark_paid" value="1" class="rounded border-gray-300 dark:border-white/10 text-brand-600 focus:ring-brand-500"> Mark as paid now
                </label>
                <button class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    <i data-lucide="check-circle-2" class="size-4"></i> Record payout
                </button>
            </div>
        </form>
    </div>

    <!-- Recorded payouts -->
    <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recorded payouts</h3>
        </div>
        <?php if (empty($payouts)): ?>
            <p class="px-5 py-8 text-sm text-center text-gray-500 dark:text-gray-400">No payouts recorded yet. Use “New payout” above to create one.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">Date</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Period</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Amount</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Method</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Slip</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Status</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Notified</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php foreach ($payouts as $p):
                            $stColor = $p['status'] === 'paid' ? 'green' : 'amber';
                        ?>
                            <tr>
                                <td class="px-4 py-2.5 whitespace-nowrap text-gray-700 dark:text-gray-300"><?= esc(date('M j, Y', strtotime($p['created_at']))) ?></td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-gray-600 dark:text-gray-400 text-xs">
                                    <?= $p['period_from'] && $p['period_to'] ? esc(date('M j', strtotime($p['period_from'])).' – '.date('M j, Y', strtotime($p['period_to']))) : '—' ?>
                                </td>
                                <td class="px-4 py-2.5 text-right font-semibold text-brand-600 dark:text-brand-400 whitespace-nowrap">LKR <?= number_format((float)$p['amount'], 2) ?></td>
                                <td class="px-4 py-2.5 capitalize text-gray-700 dark:text-gray-300"><?= esc(str_replace('_',' ', (string)($p['method'] ?: '—'))) ?></td>
                                <td class="px-4 py-2.5">
                                    <?php if (!empty($p['slip_path'])): ?>
                                        <a href="<?= base_url('uploads/'.$p['slip_path']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline"><i data-lucide="paperclip" class="size-3.5"></i> View</a>
                                    <?php else: ?>
                                        <form method="POST" action="<?= site_url('admin/staff/'.(int)$staff['id'].'/payouts/'.$p['id'].'/slip') ?>" enctype="multipart/form-data" class="flex items-center gap-1">
                                            <?= csrf_field() ?>
                                            <input type="file" name="slip" accept="image/*,application/pdf" required class="w-24 text-[10px] text-gray-600 dark:text-gray-400 file:mr-1 file:rounded file:border-0 file:bg-gray-100 dark:file:bg-white/10 file:px-1.5 file:py-0.5 file:text-[10px] dark:file:text-gray-200">
                                            <button class="text-gray-500 hover:text-brand-600 dark:hover:text-brand-400" title="Upload slip"><i data-lucide="upload" class="size-3.5"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2.5"><span class="inline-flex items-center rounded-full bg-<?= $stColor ?>-100 dark:bg-<?= $stColor ?>-500/20 px-2 py-0.5 text-xs font-medium text-<?= $stColor ?>-700 dark:text-<?= $stColor ?>-300 capitalize"><?= esc($p['status']) ?></span></td>
                                <td class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    <?php if (!empty($p['notified_at'])): ?>
                                        <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400"><i data-lucide="check" class="size-3.5"></i><?= esc(date('M j', strtotime($p['notified_at']))) ?></span>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <form method="POST" action="<?= site_url('admin/staff/'.(int)$staff['id'].'/payouts/'.$p['id'].'/notify') ?>" class="inline">
                                        <?= csrf_field() ?>
                                        <button class="inline-flex items-center gap-1 rounded-md bg-brand-50 dark:bg-brand-500/15 px-2 py-1 text-[11px] font-semibold text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-500/25" title="Notify stylist">
                                            <i data-lucide="send" class="size-3.5"></i> Notify
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= site_url('admin/staff/'.(int)$staff['id'].'/payouts/'.$p['id']) ?>" class="inline" onsubmit="return confirm('Delete this payout record?');">
                                        <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                        <button class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-500/10 px-1.5 py-1 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20" title="Delete"><i data-lucide="trash-2" class="size-3.5"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Per-month breakdown -->
    <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Monthly payout history</h3>
        </div>
        <?php if (empty($rows)): ?>
            <p class="px-5 py-8 text-sm text-center text-gray-500 dark:text-gray-400">No payments attributed to this stylist in the selected range.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">Month</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Payments</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Revenue</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Commission (<?= number_format($pct, 1) ?>%)</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php foreach ($rows as $r):
                            $monthFirst = $r['period'] . '-01';
                            $monthLast  = date('Y-m-t', strtotime($monthFirst));
                            $monthPdf   = site_url('admin/reports/staff/' . (int)$staff['id'] . '/payout?preset=custom&from=' . $monthFirst . '&to=' . $monthLast);
                        ?>
                            <tr>
                                <td class="px-4 py-2.5 text-sm font-medium text-gray-900 dark:text-white"><?= esc(date('F Y', strtotime($monthFirst))) ?></td>
                                <td class="px-4 py-2.5 text-right text-sm text-gray-700 dark:text-gray-300"><?= (int) $r['payments_n'] ?></td>
                                <td class="px-4 py-2.5 text-right text-sm text-gray-900 dark:text-white">LKR <?= number_format((float)$r['revenue'], 2) ?></td>
                                <td class="px-4 py-2.5 text-right text-sm font-semibold text-brand-600 dark:text-brand-400">LKR <?= number_format((float)$r['commission'], 2) ?></td>
                                <td class="px-4 py-2.5 text-right">
                                    <a href="<?= esc($monthPdf) ?>" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-brand-600"><i data-lucide="download" class="size-3.5"></i> PDF</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <td class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300">Total</td>
                            <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700 dark:text-gray-300"><?= array_sum(array_column($rows, 'payments_n')) ?></td>
                            <td class="px-4 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">LKR <?= number_format($totalRevenue, 2) ?></td>
                            <td class="px-4 py-2 text-right text-sm font-bold text-brand-600 dark:text-brand-400">LKR <?= number_format($totalCommission, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
