<?php
/**
 * Month calendar widget.
 *
 * @var string $month        'YYYY-MM'
 * @var string $monthStart   'YYYY-MM-01'
 * @var array  $apptsByDay   ['YYYY-MM-DD' => [ {appt}, ... ], ...]
 * @var int    $totalMonthAppts
 */

$first       = strtotime($monthStart);
$daysInMonth = (int) date('t', $first);
$dowOfFirst  = (int) date('w', $first); // 0=Sun … 6=Sat
$prevMonth   = date('Y-m', strtotime($monthStart . ' -1 month'));
$nextMonth   = date('Y-m', strtotime($monthStart . ' +1 month'));
$todayY      = date('Y-m-d');
$monthLabel  = date('F Y', $first);

// Build a 6×7 grid of cells. Each cell is either null (out of month) or 'YYYY-MM-DD'.
$cells = [];
// Leading blanks
for ($i = 0; $i < $dowOfFirst; $i++) $cells[] = null;
for ($d = 1; $d <= $daysInMonth; $d++) {
    $cells[] = date('Y-m-d', strtotime($monthStart . ' +' . ($d - 1) . ' days'));
}
// Pad to 42 cells (6 rows × 7 cols)
while (count($cells) < 42) $cells[] = null;

$statusColor = [
    'pending'     => 'bg-amber-400',
    'confirmed'   => 'bg-brand-500',
    'checked_in'  => 'bg-cyan-500',
    'in_progress' => 'bg-indigo-500',
    'completed'   => 'bg-green-500',
    'cancelled'   => 'bg-gray-300',
    'no_show'     => 'bg-red-400',
];
?>
<div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden"
     x-data='cal({
        apptsByDay: <?= json_encode($apptsByDay, JSON_UNESCAPED_UNICODE) ?>,
        today:      <?= json_encode($todayY) ?>,
        monthStart: <?= json_encode($monthStart) ?>,
        statusColor: <?= json_encode($statusColor) ?>
     })'>

    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-5 py-3.5">
        <div class="flex items-center gap-2.5">
            <span class="flex size-9 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                <i data-lucide="calendar-days" class="size-4"></i>
            </span>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($monthLabel) ?></h3>
                <p class="text-xs text-gray-500 dark:text-gray-400"><?= (int) $totalMonthAppts ?> appointment<?= $totalMonthAppts === 1 ? '' : 's' ?> this month</p>
            </div>
        </div>
        <div class="flex items-center gap-1">
            <a href="<?= site_url('admin/dashboard?month=' . $prevMonth) ?>"
               class="inline-flex size-8 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Previous month">
                <i data-lucide="chevron-left" class="size-4"></i>
            </a>
            <a href="<?= site_url('admin/dashboard') ?>"
               class="rounded-md px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Today</a>
            <a href="<?= site_url('admin/dashboard?month=' . $nextMonth) ?>"
               class="inline-flex size-8 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Next month">
                <i data-lucide="chevron-right" class="size-4"></i>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px]">

        <!-- ── Month grid ── -->
        <div class="border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-white/10 p-4">
            <div class="grid grid-cols-7 text-center text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 pb-2">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>

            <div class="grid grid-cols-7 gap-1">
                <?php foreach ($cells as $cell): ?>
                    <?php if ($cell === null): ?>
                        <div class="aspect-square"></div>
                    <?php else:
                        $appts   = $apptsByDay[$cell] ?? [];
                        $count   = count($appts);
                        $isToday = $cell === $todayY;
                        $dayNum  = (int) date('j', strtotime($cell));
                    ?>
                        <button type="button"
                                @click="select('<?= esc($cell) ?>')"
                                :class="selected === '<?= esc($cell) ?>'
                                    ? 'ring-2 ring-brand-500 bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300'
                                    : '<?= $isToday ? 'ring-1 ring-brand-300 dark:ring-brand-500/40 text-brand-700 dark:text-brand-300' : 'hover:bg-gray-50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300' ?>'"
                                class="aspect-square rounded-md flex flex-col items-center justify-center gap-1 text-sm font-medium transition-colors relative">
                            <span><?= $dayNum ?></span>
                            <?php if ($count > 0): ?>
                                <div class="flex items-center gap-0.5">
                                    <?php
                                    // Up to 3 status dots
                                    $statuses = array_slice(array_unique(array_column($appts, 'status')), 0, 3);
                                    foreach ($statuses as $st):
                                        $cls = $statusColor[$st] ?? 'bg-gray-400';
                                    ?>
                                        <span class="size-1.5 rounded-full <?= $cls ?>"></span>
                                    <?php endforeach; ?>
                                </div>
                                <span class="absolute right-1 top-1 inline-flex min-w-[26px] h-[26px] items-center justify-center rounded-full bg-brand-600 px-1.5 text-sm font-bold text-white shadow-sm" title="<?= $count ?> appointment<?= $count === 1 ? '' : 's' ?>"><?= $count ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Legend -->
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-white/5 flex flex-wrap gap-x-3 gap-y-1.5 text-[10px] text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1"><span class="size-1.5 rounded-full bg-brand-500"></span>Confirmed</span>
                <span class="inline-flex items-center gap-1"><span class="size-1.5 rounded-full bg-amber-400"></span>Pending</span>
                <span class="inline-flex items-center gap-1"><span class="size-1.5 rounded-full bg-indigo-500"></span>In progress</span>
                <span class="inline-flex items-center gap-1"><span class="size-1.5 rounded-full bg-green-500"></span>Completed</span>
                <span class="inline-flex items-center gap-1"><span class="size-1.5 rounded-full bg-red-400"></span>No-show</span>
            </div>
        </div>

        <!-- ── Selected day panel ── -->
        <aside class="bg-gray-50 dark:bg-gray-900/60 flex flex-col lg:h-full">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-white/10">
                <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Selected day</p>
                <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white" x-text="selectedLabel"></p>
                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedAppts.length + ' appointment' + (selectedAppts.length === 1 ? '' : 's')"></p>
            </div>

            <div class="flex-1 overflow-auto p-3 space-y-2">
                <template x-if="!selectedAppts.length">
                    <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                        <div class="mx-auto size-10 rounded-full bg-gray-200 dark:bg-white/10 flex items-center justify-center">
                            <i data-lucide="calendar-x" class="size-5"></i>
                        </div>
                        <p class="mt-2.5 text-xs font-medium">No appointments</p>
                        <a :href="bookHref" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                            Book one <i data-lucide="arrow-right" class="size-3"></i>
                        </a>
                    </div>
                </template>

                <template x-for="a in selectedAppts" :key="a.id">
                    <a :href="'<?= site_url('admin/appointments') ?>/' + a.id"
                       class="block rounded-md bg-white dark:bg-gray-800 p-2.5 ring-1 ring-gray-200 dark:ring-white/10 hover:ring-brand-300 dark:hover:ring-brand-500/40 transition">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-semibold text-gray-900 dark:text-white" x-text="a.time"></span>
                            <span :class="badgeClass(a.status)" class="inline-flex items-center rounded-full px-1.5 py-0 text-[10px] font-semibold capitalize" x-text="a.status.replace('_', ' ')"></span>
                        </div>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white truncate" x-text="a.customer_name"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="'with ' + a.staff_name"></p>
                    </a>
                </template>
            </div>

            <div class="border-t border-gray-200 dark:border-white/10 p-3">
                <a :href="bookHref" class="block w-full text-center rounded-md bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">
                    + Book on <span x-text="selectedShort"></span>
                </a>
            </div>
        </aside>
    </div>
</div>

<script>
function cal(cfg) {
    return {
        apptsByDay: cfg.apptsByDay,
        statusColor: cfg.statusColor,
        selected: cfg.apptsByDay[cfg.today] ? cfg.today : (cfg.monthStart),

        get selectedAppts() { return this.apptsByDay[this.selected] || []; },

        get selectedLabel() {
            const d = new Date(this.selected + 'T00:00');
            return d.toLocaleDateString(undefined, { weekday:'long', year:'numeric', month:'long', day:'numeric' });
        },
        get selectedShort() {
            const d = new Date(this.selected + 'T00:00');
            return d.toLocaleDateString(undefined, { month:'short', day:'numeric' });
        },
        get bookHref() {
            // Pre-fill ?date= so the form opens with this day in the picker
            return '<?= site_url('admin/appointments/create') ?>?date=' + this.selected;
        },

        select(d) {
            this.selected = d;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        badgeClass(status) {
            const map = {
                pending:     'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                confirmed:   'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300',
                checked_in:  'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300',
                in_progress: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
                completed:   'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
                cancelled:   'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300',
                no_show:     'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
            };
            return map[status] || map.pending;
        },
    };
}
</script>
