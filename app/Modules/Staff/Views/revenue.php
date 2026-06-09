<?php
/** @var array $staff, $lines, $apptStats; @var string $from, $to; @var float $revenue, $avgTicket */
$activeTab = 'revenue';
$presets = [
    'today' => 'Today',
    '7d'    => '7 days',
    '30d'   => '30 days',
    'mtd'   => 'This month',
    'ytd'   => 'This year',
];
$curPreset = $_GET['preset'] ?? '30d';
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
        <span class="text-xs text-gray-500 dark:text-gray-400"><?= esc(date('M j', strtotime($from))) ?> — <?= esc(date('M j, Y', strtotime($to))) ?></span>
    </div>

    <!-- KPI cards -->
    <?php
    $cards = [
        ['Revenue',         'LKR ' . number_format($revenue, 0),                 'banknote',       'green'],
        ['Average ticket',  'LKR ' . number_format($avgTicket, 0),               'receipt',        'blue'],
        ['Completed appts', (int) ($apptStats['completed'] ?? 0),                'check-circle-2', 'brand'],
        ['No-shows',        (int) ($apptStats['no_shows'] ?? 0),                 'user-x',         'red'],
    ];
    ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php foreach ($cards as [$label, $val, $icon, $col]): ?>
            <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4 flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-lg bg-<?= $col ?>-100 dark:bg-<?= $col ?>-500/20 text-<?= $col ?>-600 dark:text-<?= $col ?>-300 shrink-0">
                    <i data-lucide="<?= $icon ?>" class="size-4"></i>
                </span>
                <div>
                    <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400"><?= esc($label) ?></p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white"><?= esc($val) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paid services table -->
    <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Paid services</h3>
            <span class="text-xs text-gray-500 dark:text-gray-400"><?= count($lines) ?> payment<?= count($lines) === 1 ? '' : 's' ?></span>
        </div>
        <?php if (empty($lines)): ?>
            <p class="px-5 py-8 text-sm text-center text-gray-500 dark:text-gray-400">No paid services for this stylist in the selected range.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">Date</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Invoice</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Customer</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Method</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php foreach ($lines as $l): ?>
                            <tr>
                                <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap"><?= esc(date('M j, Y H:i', strtotime($l['paid_at']))) ?></td>
                                <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-200"><?= esc($l['invoice_no'] ?: '—') ?></td>
                                <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-200"><?= esc($l['customer_name'] ?: 'Walk-in') ?></td>
                                <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 capitalize"><?= esc($l['method']) ?></td>
                                <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900 dark:text-white">LKR <?= number_format((float)$l['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">Total</td>
                            <td class="px-4 py-2 text-right text-sm font-bold text-brand-600 dark:text-brand-400">LKR <?= number_format($revenue, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
