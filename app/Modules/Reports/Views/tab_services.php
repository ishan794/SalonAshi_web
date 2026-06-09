<?php /** @var array $data */
$rows = $data['rows'];
$totalBookings = array_sum(array_column($rows, 'bookings'));
$totalRev      = array_sum(array_column($rows, 'revenue'));
?>
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top services</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400"><?= $totalBookings ?> booking<?= $totalBookings === 1 ? '' : 's' ?> · LKR <?= number_format($totalRev, 0) ?> total</p>
        </div>
        <a href="<?= site_url('admin/reports/services/csv?preset='.esc($preset).'&from='.esc($from).'&to='.esc($to)) ?>"
           class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="download" class="size-4"></i> CSV
        </a>
    </div>

    <?php if (empty($rows)): ?>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-10 text-center shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <i data-lucide="bar-chart-3" class="mx-auto size-10 text-gray-300 dark:text-gray-600"></i>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No service bookings in this range.</p>
        </div>
    <?php else: ?>
        <!-- Chart -->
        <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Top 10 by revenue</h3>
            <div class="relative h-72"><canvas id="svcChart"></canvas></div>
        </div>

        <!-- Table -->
        <div class="rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">#</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Service</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Bookings</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Revenue</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Share</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Total minutes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php foreach ($rows as $i => $r):
                            $share = $totalRev > 0 ? round(((float)$r['revenue'] / $totalRev) * 100, 1) : 0;
                        ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400"><?= $i + 1 ?></td>
                                <td class="px-4 py-2.5 text-sm font-medium text-gray-900 dark:text-white"><?= esc($r['service'] ?: '(deleted service)') ?></td>
                                <td class="px-4 py-2.5 text-sm text-right text-gray-700 dark:text-gray-300"><?= (int)$r['bookings'] ?></td>
                                <td class="px-4 py-2.5 text-sm text-right font-semibold text-gray-900 dark:text-white">LKR <?= number_format((float)$r['revenue'], 0) ?></td>
                                <td class="px-4 py-2.5 text-sm text-right text-gray-600 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-12 h-1.5 rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden">
                                            <span class="block h-full bg-brand-500" style="width:<?= min($share, 100) ?>%"></span>
                                        </span>
                                        <?= $share ?>%
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-sm text-right text-gray-500 dark:text-gray-400"><?= (int)$r['total_minutes'] ?> min</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        (function() {
          function init() {
            const el = document.getElementById('svcChart');
            if (!el || !window.Chart) return setTimeout(init, 100);
            const isDark = document.documentElement.classList.contains('dark');
            const tick = isDark ? '#9ca3af' : '#6b7280';
            const grid = isDark ? 'rgba(255,255,255,0.06)' : '#e5e7eb';
            const top10 = <?= json_encode(array_slice($rows, 0, 10)) ?>;
            new Chart(el, {
              type: 'bar',
              data: {
                labels: top10.map(r => r.service || '(deleted)'),
                datasets: [{ label: 'Revenue', data: top10.map(r => +r.revenue), backgroundColor: '#db2777', borderRadius: 6 }]
              },
              options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                  x: { ticks: { color: tick, callback: v => 'LKR ' + v.toLocaleString() }, grid: { color: grid }, beginAtZero: true },
                  y: { ticks: { color: tick }, grid: { display: false } },
                }
              }
            });
          }
          init();
        })();
        </script>
    <?php endif; ?>
</div>
