<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data); // customer, upcoming, past, lastTreatment, invoices, payments

$statusColors = [
    'pending' => 'amber','confirmed' => 'blue','checked_in' => 'cyan',
    'in_progress' => 'indigo','completed' => 'green','cancelled' => 'gray','no_show' => 'red',
];
$invStatusColors = ['draft' => 'gray', 'unpaid' => 'amber', 'partial' => 'orange', 'paid' => 'green', 'void' => 'gray', 'cancelled' => 'gray'];
$loyaltyEnabled = $s->get('loyalty_enabled') === '1';
?>
<section class="pt-28 pb-10 bg-gradient-to-br from-amber-50 to-white dark:from-gray-950 dark:to-gray-950 dark:bg-gray-950 dark:bg-none">
    <div class="mx-auto max-w-5xl px-6 lg:px-8">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4 min-w-0">
                <div class="size-14 rounded-full bg-gradient-to-br from-brand-400 to-amber-600 flex items-center justify-center text-white text-2xl font-semibold shadow-lg shadow-brand-500/20 shrink-0">
                    <?= esc(strtoupper(substr($customer['full_name'], 0, 1))) ?>
                </div>
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-wide font-semibold text-brand-600 dark:text-brand-400"><?= lang('Site.portal.welcome_back') ?></p>
                    <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-gray-900 dark:text-white font-display truncate"><?= esc($customer['full_name']) ?></h1>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"><?= esc($customer['email'] ?: phone_local($customer['mobile'])) ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="<?= site_url('book') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-500 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                    <i data-lucide="calendar-plus" class="size-4"></i> <?= lang('Site.hero.book_btn') ?>
                </a>
                <a href="<?= site_url('portal/logout') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-white dark:bg-white/5 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50">
                    <i data-lucide="log-out" class="size-4"></i> <?= lang('Site.portal.logout') ?>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-8 lg:py-12 bg-white dark:bg-gray-950">
    <div class="mx-auto max-w-5xl px-6 lg:px-8 space-y-8">

        <!-- ── KPI row ── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10 p-4">
                <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400"><?= lang('Site.portal.kpi_upcoming') ?></p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"><?= count($upcoming) ?></p>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10 p-4">
                <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400"><?= lang('Site.portal.kpi_visits') ?></p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"><?= count(array_filter($past, fn($a) => ($a['status'] ?? '') === 'completed')) ?></p>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10 p-4">
                <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400"><?= lang('Site.portal.kpi_invoices') ?></p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"><?= count($invoices) ?></p>
            </div>
            <?php if ($loyaltyEnabled): ?>
                <div class="rounded-xl bg-gradient-to-br from-brand-500 to-amber-600 p-4 text-white shadow-lg shadow-brand-500/20">
                    <p class="text-xs uppercase tracking-wide font-semibold text-white/80"><?= lang('Site.portal.kpi_points') ?></p>
                    <p class="mt-1 text-2xl font-bold"><?= number_format((int)($customer['loyalty_points'] ?? 0)) ?></p>
                    <p class="text-[10px] mt-0.5 uppercase tracking-wide text-white/70"><?= esc($customer['membership'] ?? 'bronze') ?> tier</p>
                </div>
            <?php else: ?>
                <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10 p-4">
                    <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400"><?= lang('Site.portal.kpi_member') ?></p>
                    <p class="mt-1 text-base font-bold text-gray-900 dark:text-white"><?= esc(date('M Y', strtotime($customer['created_at']))) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Upcoming bookings ── -->
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                <i data-lucide="calendar-clock" class="size-5 text-brand-500"></i> <?= lang('Site.portal.upcoming_heading') ?>
            </h2>
            <?php if (empty($upcoming)): ?>
                <div class="mt-3 rounded-xl bg-gray-50 dark:bg-white/5 p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    <?= lang('Site.portal.no_upcoming') ?>
                </div>
            <?php else: ?>
                <div class="mt-3 space-y-2">
                    <?php foreach ($upcoming as $a): $c = $statusColors[$a['status']] ?? 'gray'; ?>
                        <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10 p-4 flex items-start gap-4">
                            <div class="text-center bg-brand-50 dark:bg-brand-500/15 rounded-lg px-3 py-2 shrink-0">
                                <p class="text-[10px] uppercase tracking-wide text-brand-700 dark:text-brand-300 font-bold"><?= esc(date('M', strtotime($a['start_at']))) ?></p>
                                <p class="text-xl font-bold text-brand-700 dark:text-brand-300 leading-none"><?= esc(date('j', strtotime($a['start_at']))) ?></p>
                                <p class="text-[10px] text-brand-700 dark:text-brand-300 mt-0.5"><?= esc(date('H:i', strtotime($a['start_at']))) ?></p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white">#<?= esc($a['code']) ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?= lang('Site.confirm.with', [esc($a['staff_name'] ?? '—')]) ?></p>
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 text-<?= $c ?>-700 dark:text-<?= $c ?>-300 px-2 py-0.5 text-[10px] font-semibold uppercase"><?= esc($a['status']) ?></span>
                                <!-- Self-service actions -->
                                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                                    <a href="<?= site_url('book/confirm/' . esc($a['code'])) ?>" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline"><?= lang('Site.portal.view') ?></a>
                                    <a href="<?= site_url('portal/booking/' . (int)$a['id'] . '/reschedule') ?>" class="font-semibold text-gray-600 dark:text-gray-300 hover:text-brand-600 inline-flex items-center gap-1"><i data-lucide="calendar-clock" class="size-3.5"></i> <?= lang('Site.reschedule.action') ?></a>
                                    <form method="POST" action="<?= site_url('portal/booking/' . (int)$a['id'] . '/cancel') ?>" onsubmit="return confirm('<?= esc(lang('Site.reschedule.cancel_confirm'), 'js') ?>')" class="inline">
                                        <?= csrf_field() ?>
                                        <button class="font-semibold text-red-600 dark:text-red-400 hover:underline inline-flex items-center gap-1"><i data-lucide="x" class="size-3.5"></i> <?= lang('Site.reschedule.cancel_action') ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Last treatment ── -->
        <?php if ($lastTreatment): ?>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                    <i data-lucide="sparkles" class="size-5 text-brand-500"></i> <?= lang('Site.portal.last_treatment') ?>
                </h2>
                <div class="mt-3 rounded-2xl bg-gradient-to-br from-brand-50 to-amber-50 dark:from-brand-500/10 dark:to-amber-500/10 ring-1 ring-brand-200 dark:ring-brand-500/30 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc(date('l, F j, Y', strtotime($lastTreatment['appt']['start_at']))) ?></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400"><?= lang('Site.confirm.with', [esc($lastTreatment['appt']['staff_name'] ?? '—')]) ?> · <?= esc($lastTreatment['appt']['code']) ?></p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-500/20 text-green-700 dark:text-green-300 px-2 py-0.5 text-[10px] font-semibold uppercase">Completed</span>
                    </div>
                    <ul class="mt-4 space-y-1.5">
                        <?php foreach ($lastTreatment['services'] as $sv): ?>
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-200"><?= esc($sv['service_name'] ?? '—') ?></span>
                                <span class="text-gray-600 dark:text-gray-400">LKR <?= number_format((float)$sv['price'], 0) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= site_url('book') ?>" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 dark:text-brand-300 hover:underline">
                        <i data-lucide="repeat-2" class="size-3.5"></i> <?= lang('Site.portal.book_again') ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Two columns: Past bookings + Invoices ── -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Past bookings -->
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                    <i data-lucide="history" class="size-5 text-brand-500"></i> <?= lang('Site.portal.history_heading') ?>
                </h2>
                <?php if (empty($past)): ?>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400"><?= lang('Site.portal.no_history') ?></p>
                <?php else: ?>
                    <ul class="mt-3 divide-y divide-gray-100 dark:divide-white/10 rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10">
                        <?php foreach (array_slice($past, 0, 8) as $a): $c = $statusColors[$a['status']] ?? 'gray'; ?>
                            <li class="px-4 py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?= esc(date('M j, Y', strtotime($a['start_at']))) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?= esc($a['staff_name'] ?? '—') ?> · <?= esc($a['code']) ?></p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 text-<?= $c ?>-700 dark:text-<?= $c ?>-300 px-2 py-0.5 text-[10px] font-semibold uppercase shrink-0"><?= esc($a['status']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Invoices -->
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                    <i data-lucide="receipt" class="size-5 text-brand-500"></i> <?= lang('Site.portal.invoices_heading') ?>
                </h2>
                <?php if (empty($invoices)): ?>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400"><?= lang('Site.portal.no_invoices') ?></p>
                <?php else: ?>
                    <ul class="mt-3 divide-y divide-gray-100 dark:divide-white/10 rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10">
                        <?php foreach ($invoices as $inv): $c = $invStatusColors[$inv['status']] ?? 'gray'; ?>
                            <li>
                                <a href="<?= site_url('portal/invoice/' . (int)$inv['id']) ?>" class="px-4 py-3 flex items-center justify-between gap-3 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?= esc($inv['code']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc(date('M j, Y', strtotime($inv['created_at']))) ?> · LKR <?= number_format((float)$inv['total'], 2) ?></p>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 text-<?= $c ?>-700 dark:text-<?= $c ?>-300 px-2 py-0.5 text-[10px] font-semibold uppercase shrink-0"><?= esc($inv['status']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Recent payments ── -->
        <?php if (! empty($payments)): ?>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                    <i data-lucide="banknote" class="size-5 text-brand-500"></i> <?= lang('Site.portal.payments_heading') ?>
                </h2>
                <div class="mt-3 overflow-x-auto rounded-xl ring-1 ring-gray-200 dark:ring-white/10">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold"><?= lang('Site.portal.col_date') ?></th>
                                <th class="px-4 py-2 text-left font-semibold"><?= lang('Site.portal.col_invoice') ?></th>
                                <th class="px-4 py-2 text-left font-semibold"><?= lang('Site.portal.col_method') ?></th>
                                <th class="px-4 py-2 text-right font-semibold"><?= lang('Site.portal.col_amount') ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10 bg-white dark:bg-gray-900">
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-200"><?= esc(date('M j, Y H:i', strtotime($p['paid_at']))) ?></td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-200"><?= esc($p['invoice_code']) ?></td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400"><?= esc($p['method']) ?></td>
                                    <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-white">LKR <?= number_format((float)$p['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>
