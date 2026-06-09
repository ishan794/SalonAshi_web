<?php
/** @var string $active, $subview, $from, $to, $preset; array $data */
$tabs = [
    'overview' => ['label' => 'Overview',  'icon' => 'gauge',         'desc' => 'KPIs and revenue trend'],
    'sales'    => ['label' => 'Sales',     'icon' => 'banknote',      'desc' => 'Collected vs outstanding · methods'],
    'services' => ['label' => 'Services',  'icon' => 'sparkles',      'desc' => 'Top services by bookings + revenue'],
    'staff'    => ['label' => 'Staff',     'icon' => 'user-cog',      'desc' => 'Per-stylist appts + revenue + commission'],
];

$presets = [
    'today' => 'Today',
    '7d'    => 'Last 7 days',
    '30d'   => 'Last 30 days',
    'mtd'   => 'Month to date',
    'ytd'   => 'Year to date',
    'custom'=> 'Custom',
];
?>
<div class="space-y-5">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Reports</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Money, bookings, and stylist performance — at a glance.</p>
    </div>

    <!-- Date range bar -->
    <form method="GET" action="<?= site_url('admin/reports/' . $active) ?>"
          class="flex flex-wrap items-end gap-3 rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
        <div class="flex flex-wrap gap-1.5">
            <?php foreach ($presets as $key => $label):
                if ($key === 'custom') continue;
                $isActive = $preset === $key;
            ?>
                <a href="<?= site_url('admin/reports/' . $active . '?preset=' . $key) ?>"
                   class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors <?= $isActive
                       ? 'bg-brand-600 text-white shadow-sm'
                       : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10' ?>">
                    <?= esc($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="ml-auto flex items-end gap-2">
            <input type="hidden" name="preset" value="custom">
            <div>
                <label class="block text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">From</label>
                <input type="date" name="from" value="<?= esc($from) ?>"
                       class="mt-0.5 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">To</label>
                <input type="date" name="to" value="<?= esc($to) ?>"
                       class="mt-0.5 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <button class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="filter" class="size-4"></i> Apply
            </button>
        </div>
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar tabs -->
        <aside class="lg:col-span-1">
            <nav class="space-y-1">
                <?php foreach ($tabs as $key => $t):
                    $isActive = $active === $key;
                ?>
                    <a href="<?= site_url('admin/reports/' . $key . '?preset=' . $preset . '&from=' . $from . '&to=' . $to) ?>"
                       class="group flex items-start gap-3 rounded-md px-3 py-2.5 transition-colors <?= $isActive
                           ? 'bg-brand-50 dark:bg-brand-500/10'
                           : 'hover:bg-gray-50 dark:hover:bg-white/5' ?>">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-md <?= $isActive
                            ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30'
                            : 'bg-gray-100 text-gray-500 group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-white/5 dark:text-gray-400 dark:group-hover:bg-brand-500/15 dark:group-hover:text-brand-300' ?>">
                            <i data-lucide="<?= esc($t['icon']) ?>" class="size-4"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold <?= $isActive ? 'text-brand-700 dark:text-brand-300' : 'text-gray-900 dark:text-white' ?>">
                                <?= esc($t['label']) ?>
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate"><?= esc($t['desc']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <p class="mt-4 px-3 text-[11px] text-gray-500 dark:text-gray-400">
                Range: <strong class="text-gray-700 dark:text-gray-300"><?= esc(date('M j, Y', strtotime($from))) ?> – <?= esc(date('M j, Y', strtotime($to))) ?></strong>
            </p>
        </aside>

        <!-- Active report -->
        <section class="lg:col-span-3">
            <?= view($subview, ['data' => $data, 'from' => $from, 'to' => $to, 'preset' => $preset, 'active' => $active]) ?>
        </section>
    </div>
</div>

<!-- Chart.js loaded once on every reports page -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
