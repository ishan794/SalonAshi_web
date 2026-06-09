<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data); // invoice, items, payments
$invStatusColors = ['draft' => 'gray', 'unpaid' => 'amber', 'partial' => 'orange', 'paid' => 'green', 'void' => 'gray', 'cancelled' => 'gray'];
$c = $invStatusColors[$invoice['status']] ?? 'gray';
?>
<section class="pt-28 pb-10 bg-gradient-to-br from-amber-50 to-white dark:from-gray-950 dark:to-gray-950 dark:bg-gray-950 dark:bg-none">
    <div class="mx-auto max-w-3xl px-6 lg:px-8">
        <a href="<?= site_url('portal/dashboard') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:text-brand-600">
            <i data-lucide="arrow-left" class="size-4"></i> <?= lang('Site.portal.back_to_account') ?>
        </a>
        <div class="mt-3 flex items-start justify-between gap-3 flex-wrap">
            <div>
                <p class="text-xs uppercase tracking-wide font-semibold text-brand-600 dark:text-brand-400"><?= lang('Site.portal.invoice') ?></p>
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-gray-900 dark:text-white font-display"><?= esc($invoice['code']) ?></h1>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?= esc(date('F j, Y H:i', strtotime($invoice['created_at']))) ?></p>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 text-<?= $c ?>-700 dark:text-<?= $c ?>-300 px-3 py-1 text-xs font-semibold uppercase tracking-wide"><?= esc($invoice['status']) ?></span>
        </div>
    </div>
</section>

<section class="py-8 lg:py-12 bg-white dark:bg-gray-950">
    <div class="mx-auto max-w-3xl px-6 lg:px-8 space-y-6">

        <!-- Line items -->
        <div class="rounded-2xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-2.5 text-left font-semibold"><?= lang('Site.portal.col_item') ?></th>
                            <th class="px-5 py-2.5 text-right font-semibold"><?= lang('Site.portal.col_qty') ?></th>
                            <th class="px-5 py-2.5 text-right font-semibold"><?= lang('Site.portal.col_price') ?></th>
                            <th class="px-5 py-2.5 text-right font-semibold"><?= lang('Site.portal.col_subtotal') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td class="px-5 py-3 text-gray-800 dark:text-gray-100"><?= esc($it['name'] ?? '—') ?></td>
                                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300"><?= number_format((float)$it['qty'], 0) ?></td>
                                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">LKR <?= number_format((float)$it['unit_price'], 2) ?></td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">LKR <?= number_format((float)$it['line_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-white/5 text-sm">
                        <tr>
                            <td colspan="3" class="px-5 py-2 text-right text-gray-600 dark:text-gray-400"><?= lang('Site.portal.subtotal') ?></td>
                            <td class="px-5 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">LKR <?= number_format((float)$invoice['subtotal'], 2) ?></td>
                        </tr>
                        <?php if (((float)($invoice['discount'] ?? 0)) > 0): ?>
                            <tr>
                                <td colspan="3" class="px-5 py-2 text-right text-gray-600 dark:text-gray-400"><?= lang('Site.portal.discount') ?></td>
                                <td class="px-5 py-2 text-right text-green-700 dark:text-green-400">- LKR <?= number_format((float)$invoice['discount'], 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (((float)($invoice['tax'] ?? 0)) > 0): ?>
                            <tr>
                                <td colspan="3" class="px-5 py-2 text-right text-gray-600 dark:text-gray-400"><?= lang('Site.portal.tax') ?></td>
                                <td class="px-5 py-2 text-right text-gray-800 dark:text-gray-200">LKR <?= number_format((float)$invoice['tax'], 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td colspan="3" class="px-5 py-3 text-right text-base font-bold text-gray-900 dark:text-white"><?= lang('Site.portal.total') ?></td>
                            <td class="px-5 py-3 text-right text-base font-bold text-brand-600 dark:text-brand-400">LKR <?= number_format((float)$invoice['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Payment history -->
        <div>
            <h2 class="text-base font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                <i data-lucide="banknote" class="size-5 text-brand-500"></i> <?= lang('Site.portal.payment_history') ?>
            </h2>
            <?php if (empty($payments)): ?>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400"><?= lang('Site.portal.no_payments') ?></p>
            <?php else: ?>
                <ul class="mt-3 divide-y divide-gray-100 dark:divide-white/10 rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-white/10">
                    <?php foreach ($payments as $p): ?>
                        <li class="px-4 py-3 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc(date('M j, Y H:i', strtotime($p['paid_at']))) ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($p['method']) ?><?= $p['reference'] ? ' · ' . esc($p['reference']) : '' ?></p>
                            </div>
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">LKR <?= number_format((float)$p['amount'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
