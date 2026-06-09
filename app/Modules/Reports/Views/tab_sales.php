<?php /** @var array $data */
$t = $data['totals']; $methods = $data['methods']; $daily = $data['daily'];
$collectionRate = $t['billed'] > 0 ? round(($t['collected'] / $t['billed']) * 100, 1) : 0;
?>
<div class="space-y-5">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="receipt" class="size-3.5"></i> Billed</div>
            <div class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-white">LKR <?= number_format($t['billed'], 0) ?></div>
            <p class="text-xs text-gray-500 dark:text-gray-400"><?= $t['invoices_n'] ?> invoice<?= $t['invoices_n'] === 1 ? '' : 's' ?></p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-green-600 dark:text-green-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="check-circle-2" class="size-3.5"></i> Collected</div>
            <div class="mt-1.5 text-2xl font-bold text-green-600 dark:text-green-400">LKR <?= number_format($t['collected'], 0) ?></div>
            <p class="text-xs text-gray-500 dark:text-gray-400"><?= $collectionRate ?>% collection rate</p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="alert-circle" class="size-3.5"></i> Outstanding</div>
            <div class="mt-1.5 text-2xl font-bold text-amber-600 dark:text-amber-400">LKR <?= number_format($t['outstanding'], 0) ?></div>
        </div>
        <a href="<?= site_url('admin/reports/sales/csv?preset='.esc($preset).'&from='.esc($from).'&to='.esc($to)) ?>"
           class="rounded-lg bg-brand-600 hover:bg-brand-700 p-4 shadow-sm ring-1 ring-brand-500 flex items-center justify-center gap-2 text-white">
            <i data-lucide="download" class="size-4"></i>
            <span class="text-sm font-semibold">Download CSV</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Donut: payment methods -->
        <div class="lg:col-span-1 rounded-lg bg-white dark:bg-gray-800 p-5 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">By payment method</h3>
            <?php if (empty($methods)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No payments in this range.</p>
            <?php else: ?>
                <div class="relative h-56"><canvas id="methodsChart"></canvas></div>
                <ul class="mt-4 space-y-1.5 text-sm">
                    <?php
                    $colors = ['#db2777','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444'];
                    foreach ($methods as $i => $m): ?>
                        <li class="flex items-center gap-2">
                            <span class="size-2.5 rounded-sm" style="background:<?= $colors[$i % count($colors)] ?>"></span>
                            <span class="flex-1 capitalize text-gray-700 dark:text-gray-300"><?= esc(str_replace('_',' ', $m['method'])) ?></span>
                            <span class="text-gray-900 dark:text-white font-medium">LKR <?= number_format((float)$m['total'], 0) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Daily revenue table -->
        <div class="lg:col-span-2 rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daily revenue</h3>
            </div>
            <div class="max-h-[500px] overflow-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Date</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php foreach (array_reverse($daily) as $d): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"><?= esc(date('D, M j', strtotime($d['date']))) ?></td>
                                <td class="px-4 py-2 text-sm text-right font-medium <?= $d['revenue'] > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600' ?>">LKR <?= number_format($d['revenue'], 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($methods)): ?>
<script>
(function() {
  function init() {
    const el = document.getElementById('methodsChart');
    if (!el || !window.Chart) return setTimeout(init, 100);
    const isDark = document.documentElement.classList.contains('dark');
    new Chart(el, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode(array_map(fn($m) => ucfirst(str_replace('_',' ', $m['method'])), $methods)) ?>,
        datasets: [{
          data: <?= json_encode(array_map(fn($m) => (float)$m['total'], $methods)) ?>,
          backgroundColor: ['#db2777','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444'],
          borderWidth: 2, borderColor: isDark ? '#1f2937' : '#fff'
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '65%',
      }
    });
  }
  init();
})();
</script>
<?php endif; ?>
