<?php
/** @var array $staff, string $activeTab
 * Renders the staff profile header + tab nav. Included at the top of every per-staff page.
 */
$tabs = [
    'profile'  => ['label' => 'Profile',   'icon' => 'user-cog',    'href' => site_url('admin/staff/' . (int)$staff['id'] . '/edit')],
    'schedule' => ['label' => 'Schedule',  'icon' => 'calendar-days','href' => site_url('admin/staff/' . (int)$staff['id'] . '/calendar')],
    'revenue'  => ['label' => 'Revenue',   'icon' => 'trending-up', 'href' => site_url('admin/staff/' . (int)$staff['id'] . '/revenue')],
    'payouts'  => ['label' => 'Payouts',   'icon' => 'banknote',    'href' => site_url('admin/staff/' . (int)$staff['id'] . '/payouts')],
];
$activeTab = $activeTab ?? 'profile';
$active    = ($staff['is_active'] ?? 1) ? true : false;
?>
<div class="mb-5 space-y-4">
    <!-- Back link -->
    <a href="<?= site_url('admin/staff') ?>" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400">
        <i data-lucide="arrow-left" class="size-3.5"></i> All staff
    </a>

    <!-- Profile header card -->
    <div class="rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 p-5 flex items-start gap-4">
        <div class="size-14 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-white text-2xl font-semibold shadow-md shrink-0">
            <?= esc(strtoupper(substr($staff['full_name'] ?? '?', 0, 1))) ?>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white truncate"><?= esc($staff['full_name'] ?? 'New staff') ?></h2>
                <?php if (! empty($staff['id'])): ?>
                    <span class="inline-flex items-center gap-1 rounded-full <?= $active ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400' ?> px-2 py-0.5 text-[10px] font-semibold uppercase">
                        <span class="size-1.5 rounded-full <?= $active ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                        <?= $active ? 'Active' : 'Inactive' ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                <?= esc($staff['role'] ?: 'Stylist') ?>
                <?php if (! empty($staff['commission_pct'])): ?> · <?= number_format((float)$staff['commission_pct'], 1) ?>% commission<?php endif; ?>
                <?php if (! empty($staff['email'])): ?> · <?= esc($staff['email']) ?><?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Tab nav -->
    <?php if (! empty($staff['id'])): ?>
        <div class="flex flex-wrap gap-1 bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 rounded-lg p-1 overflow-x-auto">
            <?php foreach ($tabs as $key => $t): $is = $activeTab === $key; ?>
                <a href="<?= esc($t['href']) ?>"
                   class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold whitespace-nowrap <?= $is
                       ? 'bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300'
                       : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5' ?>">
                    <i data-lucide="<?= esc($t['icon']) ?>" class="size-3.5"></i> <?= esc($t['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
