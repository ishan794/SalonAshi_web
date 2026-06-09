<?php
/** @var callable $isActive */

// Group items can include an optional 4th element: a permission slug. If set,
// the item is hidden when the current user lacks the perm (super_admin sees all).
$rawGroups = [
    ['Overview', 'gauge-circle', [
        ['admin/dashboard',          'Dashboard',     'layout-dashboard'],
        ['admin/pos',                'POS',           'scan-line'],
    ]],
    ['Schedule', 'calendar-clock', [
        ['admin/appointments',              'Appointments',          'calendar-days'],
        ['admin/appointments/cancellations','Cancellations / No-shows','user-x'],
    ]],
    ['People', 'users-round', [
        ['admin/customers',          'Customers',     'users'],
        ['admin/staff',              'Staff',         'user-cog'],
    ]],
    ['Catalog', 'sparkles', [
        ['admin/services',           'Services',      'sparkles'],
        ['admin/service-categories', 'Categories',    'tag'],
        ['admin/service-types',      'Service types', 'layers'],
    ]],
    ['Billing', 'receipt-text', [
        ['admin/billing/invoices',   'Invoices',      'receipt'],
        ['admin/billing/payments',   'Payments',      'banknote'],
        ['admin/billing/payouts',    'Payouts',       'hand-coins'],
    ]],
    ['Organisation', 'building-2', [
        ['admin/branches',           'Branches',      'store'],
    ]],
    ['Insights', 'trending-up', [
        ['admin/reports/overview',   'Reports',       'bar-chart-3'],
        ['admin/reviews',            'Reviews',       'star'],
    ]],
    ['System', 'settings-2', [
        ['admin/notifications',      'Notifications', 'bell'],
        ['admin/activity-log',       'Activity log',  'list-checks'],
        ['admin/settings/general',   'Settings',      'settings', 'settings.view'],
    ]],
];

// Filter items by permission, drop empty groups.
// Tag each group with the slug used as a key for collapse state.
$groups = [];
foreach ($rawGroups as [$label, $groupIcon, $items]) {
    $kept = [];
    $hasActive = false;
    foreach ($items as $item) {
        $perm = $item[3] ?? null;
        if ($perm && ! auth_has($perm)) continue;
        if ($isActive($item[0])) $hasActive = true;
        $kept[] = $item;
    }
    if ($kept) $groups[] = [
        'label'     => $label,
        'icon'      => $groupIcon,
        'slug'      => strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label)),
        'items'     => $kept,
        'hasActive' => $hasActive,
    ];
}

// Build JSON map of which groups should start expanded (containing active page, OR if no group has active page, all expand).
$initialOpen = [];
$anyActive   = false;
foreach ($groups as $g) {
    if ($g['hasActive']) { $initialOpen[$g['slug']] = true; $anyActive = true; }
}
if (! $anyActive) foreach ($groups as $g) $initialOpen[$g['slug']] = true;
?>
<div class="flex grow flex-col gap-y-4 overflow-y-auto sb-scroll bg-white px-3 pb-4 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-white/10"
     x-data='{
         open: (() => {
             try { const s = JSON.parse(localStorage.getItem("saloncms_sidebar_open") || "null"); return s || <?= json_encode($initialOpen) ?>; }
             catch (e) { return <?= json_encode($initialOpen) ?>; }
         })(),
         toggle(key) { this.open[key] = !this.open[key]; localStorage.setItem("saloncms_sidebar_open", JSON.stringify(this.open)); }
     }'>

    <!-- Brand -->
    <div class="flex h-16 shrink-0 items-center justify-center border-b border-gray-100 dark:border-white/5 px-2">
        <img src="<?= base_url('uploads/logo.png?v=3') ?>" alt="Salon Ashi" class="h-12 w-auto">
    </div>

    <!-- Nav -->
    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-1">

            <?php foreach ($groups as $g): ?>
                <li>
                    <!-- Group header (collapsible) -->
                    <button type="button" @click="toggle('<?= esc($g['slug']) ?>')"
                            class="group flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-700 dark:hover:text-gray-200">
                        <i data-lucide="<?= esc($g['icon']) ?>" class="size-3.5 text-gray-400 dark:text-gray-500"></i>
                        <span class="flex-1 text-left"><?= esc($g['label']) ?></span>
                        <i data-lucide="chevron-down" class="size-3.5 text-gray-400 dark:text-gray-500 transition-transform"
                           :class="open['<?= esc($g['slug']) ?>'] ? 'rotate-0' : '-rotate-90'"></i>
                    </button>

                    <!-- Items -->
                    <ul role="list" class="mt-0.5 space-y-0.5"
                        x-show="open['<?= esc($g['slug']) ?>']" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <?php foreach ($g['items'] as [$href, $label, $icon]): $active = $isActive($href); ?>
                            <li>
                                <a href="<?= site_url($href) ?>"
                                   class="group flex items-center gap-x-3 rounded-md py-2 pl-3 pr-2 text-sm font-medium transition-colors relative <?= $active
                                       ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300'
                                       : 'text-gray-700 hover:bg-gray-50 hover:text-brand-700 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' ?>">
                                    <?php if ($active): ?>
                                        <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r bg-brand-500"></span>
                                    <?php endif; ?>
                                    <i data-lucide="<?= esc($icon) ?>" class="size-5 shrink-0 <?= $active
                                        ? 'text-brand-600 dark:text-brand-300'
                                        : 'text-gray-400 group-hover:text-brand-600 dark:text-gray-500 dark:group-hover:text-white' ?>"></i>
                                    <span class="truncate flex-1"><?= esc($label) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>

            <!-- Sign-out pinned to bottom -->
            <li class="mt-auto pt-3 border-t border-gray-100 dark:border-white/5">
                <a href="<?= site_url('logout') ?>"
                   class="group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-700 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-300">
                    <i data-lucide="log-out" class="size-5 shrink-0 text-gray-400 group-hover:text-red-600 dark:text-gray-500 dark:group-hover:text-red-300"></i>
                    Sign out
                </a>
            </li>
        </ul>
    </nav>
</div>
