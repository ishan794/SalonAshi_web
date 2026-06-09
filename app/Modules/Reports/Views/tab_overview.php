<?php /** @var array $data */
$k = $data['kpis'];
$series = $data['daily'];
?>
<div class="space-y-5">
    <!-- KPI tiles -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-green-600 dark:text-green-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="banknote" class="size-3.5"></i> Revenue</div>
            <div class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-white">LKR <?= number_format($k['rev'], 0) ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-brand-600 dark:text-brand-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="receipt" class="size-3.5"></i> Invoices</div>
            <div class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-white"><?= $k['inv'] ?></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">avg ticket LKR <?= number_format($k['avg'], 0) ?></p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="calendar-days" class="size-3.5"></i> Appointments</div>
            <div class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-white"><?= $k['appts'] ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="user-plus" class="size-3.5"></i> New customers</div>
            <div class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-white"><?= $k['newCust'] ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="ban" class="size-3.5"></i> Cancellations</div>
            <div class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-white"><?= $k['cancels'] ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-red-600 dark:text-red-400 text-[11px] font-medium uppercase tracking-wide"><i data-lucide="user-x" class="size-3.5"></i> No-shows</div>
            <div class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-white"><?= $k['noShows'] ?></div>
        </div>
        <div class="col-span-2 lg:col-span-2 rounded-lg bg-gradient-to-br from-brand-50 to-purple-50 dark:from-brand-500/10 dark:to-purple-500/10 p-4 ring-1 ring-brand-200 dark:ring-brand-500/20">
            <p class="text-[11px] font-medium uppercase tracking-wide text-brand-700 dark:text-brand-300">Range</p>
            <p class="text-sm font-bold text-gray-900 dark:text-white mt-1"><?= esc(date('M j, Y', strtotime($data['from']))) ?> → <?= esc(date('M j, Y', strtotime($data['to']))) ?></p>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5"><?= count($series) ?> day<?= count($series) === 1 ? '' : 's' ?> · LKR <?= number_format($k['rev'] / max(count($series), 1), 0) ?> / day average</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Revenue per day</h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">all successful payments</span>
        </div>
        <div class="relative h-72">
            <canvas id="revChart"></canvas>
        </div>
    </div>
</div>

<script>
(function() {
    const labels = <?= json_encode(array_map(fn($r) => date('M j', strtotime($r['date'])), $series)) ?>;
    const data   = <?= json_encode(array_map(fn($r) => (float)$r['revenue'], $series)) ?>;
    const isDark = document.documentElement.classList.contains('dark');
    const tick = isDark ? '#9ca3af' : '#6b7280';
    const grid = isDark ? 'rgba(255,255,255,0.06)' : '#e5e7eb';

    function init() {
      const ctx = document.getElementById('revChart');
      if (!ctx || !window.Chart) return setTimeout(init, 100);
      new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: [{
            label: 'Revenue (LKR)',
            data,
            borderColor: '#db2777',
            backgroundColor: 'rgba(219,39,119,0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: '#db2777',
        }]},
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: tick }, grid: { color: grid } },
                y: { ticks: { color: tick, callback: v => 'LKR ' + v.toLocaleString() }, grid: { color: grid }, beginAtZero: true },
            }
        }
      });
    }
    init();
})();
</script>
