<div class="space-y-6">
    <!-- Greeting -->
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Welcome back, <?= esc(session('user.name')) ?> 👋</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Here's what's happening at your salon today.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('admin/pos') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                <i data-lucide="scan-line" class="size-4"></i> Open POS
            </a>
            <a href="<?= site_url('admin/appointments/create') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
                <i data-lucide="plus" class="size-4"></i> New booking
            </a>
        </div>
    </div>

    <!-- KPI tiles -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs"><i data-lucide="calendar-days" class="size-4"></i>Today's Appts</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['today_appts'] ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs"><i data-lucide="banknote" class="size-4"></i>Today's Revenue</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">LKR <?= number_format($stats['today_revenue'], 0) ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs"><i data-lucide="alert-circle" class="size-4"></i>Pending Balance</div>
            <div class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">LKR <?= number_format($stats['pending_balance'], 0) ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs"><i data-lucide="users" class="size-4"></i>Customers</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['total_customers'] ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs"><i data-lucide="user-cog" class="size-4"></i>Staff</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['total_staff'] ?></div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs"><i data-lucide="sparkles" class="size-4"></i>Services</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['total_services'] ?></div>
        </div>
    </div>

    <!-- ── Insight widgets: today's agenda + top customers + recent customers ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Today's agenda -->
        <div class="rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2"><i data-lucide="calendar-check" class="size-4 text-brand-500"></i> Upcoming appointments<?php if (!empty($todayAgendaTotal) && $todayAgendaTotal > count($todayAgenda)): ?> <span class="text-xs font-normal text-gray-400">(next <?= count($todayAgenda) ?> of <?= (int)$todayAgendaTotal ?>)</span><?php endif; ?></h3>
                <a href="<?= site_url('admin/appointments') ?>" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-700">Open calendar →</a>
            </div>
            <?php
            $agStatus = ['pending'=>'amber','confirmed'=>'blue','checked_in'=>'cyan','in_progress'=>'indigo','completed'=>'green','no_show'=>'red'];
            ?>
            <?php if (empty($todayAgenda)): ?>
                <p class="px-5 py-8 text-sm text-center text-gray-500 dark:text-gray-400">No appointments today.</p>
            <?php else: ?>
                <ul class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php foreach ($todayAgenda as $a): $ac = $agStatus[$a['status']] ?? 'gray'; ?>
                        <li>
                            <a href="<?= site_url('admin/appointments/' . (int)$a['id']) ?>" class="group px-5 py-2.5 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-white/5">
                                <span class="text-sm font-mono font-semibold text-gray-900 dark:text-white w-12 shrink-0"><?= esc(date('H:i', strtotime($a['start_at']))) ?></span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-brand-600 dark:group-hover:text-brand-400"><?= esc($a['customer_name'] ?: 'Walk-in') ?></span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 truncate"><?= esc($a['staff_name'] ?: '—') ?> · <?= esc($a['code']) ?></span>
                                </span>
                                <span class="inline-flex items-center rounded-full bg-<?= $ac ?>-100 dark:bg-<?= $ac ?>-500/20 text-<?= $ac ?>-700 dark:text-<?= $ac ?>-300 px-2 py-0.5 text-[10px] font-semibold uppercase shrink-0"><?= esc($a['status']) ?></span>
                                <i data-lucide="chevron-right" class="size-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-500 shrink-0"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Top customers -->
        <div class="rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top customers</h3>
                <i data-lucide="trophy" class="size-4 text-amber-500"></i>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-white/5">
                <?php foreach ($topCustomers as $i => $tc): ?>
                    <li class="px-5 py-2.5 flex items-center gap-3">
                        <span class="flex size-6 items-center justify-center rounded-full bg-<?= $i === 0 ? 'amber' : 'gray' ?>-100 dark:bg-<?= $i === 0 ? 'amber' : 'gray' ?>-500/20 text-<?= $i === 0 ? 'amber' : 'gray' ?>-700 dark:text-<?= $i === 0 ? 'amber' : 'gray' ?>-300 text-xs font-bold shrink-0"><?= $i + 1 ?></span>
                        <a href="<?= site_url('admin/customers/' . (int)$tc['id']) ?>" class="flex-1 min-w-0 hover:text-brand-600">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= esc($tc['full_name'] ?: 'Walk-in') ?></p>
                        </a>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 shrink-0">LKR <?= number_format((float)$tc['spend'], 0) ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($topCustomers)): ?><li class="px-5 py-6 text-sm text-center text-gray-500 dark:text-gray-400">No sales yet.</li><?php endif; ?>
            </ul>
        </div>

        <!-- Recent customers -->
        <div class="rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent customers</h3>
                <a href="<?= site_url('admin/customers') ?>" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300">View all →</a>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-white/5">
                <?php foreach ($recentCustomers as $c): ?>
                    <li class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-white/5">
                        <div class="size-9 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-600 dark:text-gray-400 text-sm font-medium"><?= esc(strtoupper(substr($c['full_name'],0,1))) ?></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= esc($c['full_name']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc(phone_local($c['mobile'])) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($recentCustomers)): ?><li class="px-5 py-6 text-sm text-center text-gray-500 dark:text-gray-400">No customers yet.</li><?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Calendar (full width) -->
    <?= view('App\Modules\Dashboard\Views\_calendar', [
        'month'           => $month,
        'monthStart'      => $monthStart,
        'apptsByDay'      => $apptsByDay,
        'totalMonthAppts' => $totalMonthAppts,
    ]) ?>
</div>
