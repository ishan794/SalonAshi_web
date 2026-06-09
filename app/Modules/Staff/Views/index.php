<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Staff</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Stylists, beauticians & other team members.</p>
        </div>
        <a href="<?= site_url('admin/staff/create') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="plus" class="size-4"></i> New Staff
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($rows as $r): $profileUrl = site_url('admin/staff/' . $r['id'] . '/edit'); ?>
            <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow ring-1 ring-gray-200 dark:ring-white/10 flex flex-col hover:ring-brand-300 dark:hover:ring-brand-500/40 transition">
                <!-- Click-through header (View) — whole header is the link to the staff profile -->
                <a href="<?= esc($profileUrl) ?>" class="flex items-center gap-3 group" title="View profile">
                    <div class="size-10 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center text-brand-700 dark:text-brand-300 font-semibold shrink-0">
                        <?= esc(strtoupper(substr($r['full_name'],0,1))) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-900 dark:text-white truncate group-hover:text-brand-700 dark:group-hover:text-brand-300"><?= esc($r['full_name']) ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($r['role'] ?: 'Staff') ?></p>
                    </div>
                    <i data-lucide="chevron-right" class="size-4 text-gray-300 dark:text-gray-600 group-hover:text-brand-500 transition"></i>
                </a>

                <dl class="mt-3 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <div><span class="text-gray-400 dark:text-gray-500">Mobile:</span> <?= esc($r['mobile'] ?: '—') ?></div>
                    <div><span class="text-gray-400 dark:text-gray-500">Hours:</span> <?= esc($r['working_hours'] ?: '—') ?></div>
                    <div><span class="text-gray-400 dark:text-gray-500">Commission:</span> <?= number_format((float)$r['commission_pct'], 2) ?>%</div>
                </dl>

                <!-- Primary action: View (full-width, prominent) -->
                <a href="<?= esc($profileUrl) ?>" class="mt-4 inline-flex items-center justify-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">
                    <i data-lucide="eye" class="size-3.5"></i> View profile
                </a>

                <!-- Quick-jump tab links + remove -->
                <div class="mt-2 grid grid-cols-4 gap-1.5">
                    <a href="<?= site_url('admin/staff/'.$r['id'].'/calendar') ?>" class="inline-flex items-center justify-center gap-1 rounded-md bg-gray-50 dark:bg-white/5 px-1.5 py-1.5 text-[11px] font-medium text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-white/10 hover:bg-gray-100 dark:hover:bg-white/10" title="Schedule / calendar">
                        <i data-lucide="calendar-days" class="size-3.5"></i>
                    </a>
                    <a href="<?= site_url('admin/staff/'.$r['id'].'/revenue') ?>" class="inline-flex items-center justify-center gap-1 rounded-md bg-gray-50 dark:bg-white/5 px-1.5 py-1.5 text-[11px] font-medium text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-white/10 hover:bg-gray-100 dark:hover:bg-white/10" title="Revenue">
                        <i data-lucide="trending-up" class="size-3.5"></i>
                    </a>
                    <a href="<?= site_url('admin/staff/'.$r['id'].'/payouts') ?>" class="inline-flex items-center justify-center gap-1 rounded-md bg-gray-50 dark:bg-white/5 px-1.5 py-1.5 text-[11px] font-medium text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-white/10 hover:bg-gray-100 dark:hover:bg-white/10" title="Payouts">
                        <i data-lucide="banknote" class="size-3.5"></i>
                    </a>
                    <form method="POST" action="<?= site_url('admin/staff/'.$r['id']) ?>" onsubmit="return confirm('Remove <?= esc($r['full_name'], 'js') ?>?');">
                        <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                        <button class="w-full inline-flex items-center justify-center rounded-md bg-red-50 dark:bg-red-500/10 px-1.5 py-1.5 text-[11px] font-medium text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20" title="Remove">
                            <i data-lucide="trash-2" class="size-3.5"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
