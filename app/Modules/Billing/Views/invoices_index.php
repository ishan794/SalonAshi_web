<?php
/** @var array $rows, $filters, $totals */
$statusColors = ['draft'=>'gray','unpaid'=>'amber','partial'=>'blue','paid'=>'green','refunded'=>'purple','cancelled'=>'gray'];
$status = $filters['status'] ?? '';
$q      = $filters['q']      ?? '';
$from   = $filters['from']   ?? '';
$to     = $filters['to']     ?? '';
$hasAnyFilter = $status || $q || $from || $to;

// Build base URL for status tab links that preserve other filters
$preserve = array_filter(['q' => $q, 'from' => $from, 'to' => $to], fn($v) => $v !== '' && $v !== null);
$qstr = fn(array $extra) => '?' . http_build_query(array_merge($preserve, $extra));
?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Invoices</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track invoices, balances and payments.</p>
        </div>
        <?php if (auth_has('invoices.create')): ?>
        <a href="<?= site_url('admin/billing/invoices/create') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="plus" class="size-4"></i> New Invoice
        </a>
        <?php endif; ?>
    </div>

    <!-- Filter bar -->
    <form method="GET" action="<?= site_url('admin/billing/invoices') ?>"
          class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
        <?php if ($status): ?><input type="hidden" name="status" value="<?= esc($status) ?>"><?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-5">
                <label class="block text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
                <div class="mt-1 relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-2.5 size-4 text-gray-400 dark:text-gray-500"></i>
                    <input name="q" value="<?= esc($q) ?>" placeholder="Invoice no, customer name, mobile…"
                           class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white pl-9 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">From</label>
                <input type="date" name="from" value="<?= esc($from) ?>"
                       class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">To</label>
                <input type="date" name="to" value="<?= esc($to) ?>"
                       class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div class="md:col-span-1 flex gap-1.5">
                <button class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    <i data-lucide="filter" class="size-4"></i>
                </button>
                <?php if ($hasAnyFilter): ?>
                    <a href="<?= site_url('admin/billing/invoices') ?>" title="Clear filters"
                       class="flex items-center justify-center rounded-md bg-white dark:bg-white/5 px-2 py-2 text-gray-500 dark:text-gray-400 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                        <i data-lucide="x" class="size-4"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status tabs (preserves other filters) -->
        <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-white/5">
            <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mr-1">Status:</span>
            <a href="<?= site_url('admin/billing/invoices') . $qstr([]) ?>" class="rounded-full px-3 py-1 text-xs <?= !$status ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10' ?>">All</a>
            <?php foreach (['unpaid','partial','paid','cancelled','refunded'] as $s): ?>
                <a href="<?= site_url('admin/billing/invoices') . $qstr(['status' => $s]) ?>"
                   class="rounded-full px-3 py-1 text-xs capitalize <?= $status === $s ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10' ?>">
                    <?= esc($s) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </form>

    <!-- Active filter chips + result count -->
    <?php if ($hasAnyFilter): ?>
        <div class="flex items-center flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-400">
            <span class="font-medium"><?= (int) $totals['count'] ?> result<?= $totals['count'] === 1 ? '' : 's' ?>:</span>
            <?php if ($q): ?>
                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300 px-2 py-0.5">
                    <i data-lucide="search" class="size-3"></i> "<?= esc($q) ?>"
                </span>
            <?php endif; ?>
            <?php if ($status): ?>
                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300 px-2 py-0.5 capitalize">
                    <i data-lucide="tag" class="size-3"></i> <?= esc($status) ?>
                </span>
            <?php endif; ?>
            <?php if ($from || $to): ?>
                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300 px-2 py-0.5">
                    <i data-lucide="calendar" class="size-3"></i> <?= esc($from ?: '…') ?> → <?= esc($to ?: '…') ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- KPI strip — totals across the filtered set -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-3 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Results</p>
            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white"><?= (int) $totals['count'] ?></p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-3 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <p class="text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Billed</p>
            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">LKR <?= number_format((float)$totals['billed'], 0) ?></p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-3 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <p class="text-[10px] font-medium uppercase tracking-wide text-green-600 dark:text-green-400">Paid</p>
            <p class="mt-1 text-xl font-bold text-green-600 dark:text-green-400">LKR <?= number_format((float)$totals['paid'], 0) ?></p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-3 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
            <p class="text-[10px] font-medium uppercase tracking-wide text-amber-600 dark:text-amber-400">Outstanding</p>
            <p class="mt-1 text-xl font-bold text-amber-600 dark:text-amber-400">LKR <?= number_format((float)$totals['balance'], 0) ?></p>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Invoice #</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Customer</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Stylist</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Date</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Total</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Paid</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Balance</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="9" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        <div class="mx-auto size-10 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-2">
                            <i data-lucide="<?= $hasAnyFilter ? 'search-x' : 'inbox' ?>" class="size-5"></i>
                        </div>
                        <?= $hasAnyFilter ? 'No invoices match these filters.' : 'No invoices yet.' ?>
                        <?php if ($hasAnyFilter): ?>
                            <a href="<?= site_url('admin/billing/invoices') ?>" class="text-brand-600 dark:text-brand-400 hover:underline ml-1">Clear filters</a>
                        <?php endif; ?>
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): $c = $statusColors[$r['status']] ?? 'gray'; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white font-mono"><?= esc($r['invoice_no']) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <p class="text-gray-900 dark:text-white"><?= esc($r['customer_name']) ?></p>
                            <?php if (!empty($r['customer_mobile'])): ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc(phone_local($r['customer_mobile'])) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <?php if (!empty($r['staff_name'])): ?>
                                <span class="inline-flex items-center gap-1.5 text-gray-900 dark:text-white">
                                    <i data-lucide="scissors" class="size-3.5 text-gray-400 dark:text-gray-500"></i><?= esc($r['staff_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 dark:text-gray-500">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap"><?= esc(date('M j, Y', strtotime($r['created_at']))) ?></td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white">LKR <?= number_format((float)$r['total'], 2) ?></td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">LKR <?= number_format((float)$r['paid'], 2) ?></td>
                        <td class="px-4 py-3 text-sm text-right font-medium <?= $r['balance']>0?'text-red-600 dark:text-red-400':'text-green-600 dark:text-green-400' ?>">LKR <?= number_format((float)$r['balance'], 2) ?></td>
                        <td class="px-4 py-3 text-sm"><span class="inline-flex items-center rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 px-2 py-0.5 text-xs font-medium text-<?= $c ?>-700 dark:text-<?= $c ?>-300 capitalize"><?= esc($r['status']) ?></span></td>
                        <td class="px-4 py-3 text-right text-sm"><a href="<?= site_url('admin/billing/invoices/'.$r['id']) ?>" class="text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300">Open</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (! empty($page)): ?>
            <?= view('components/pager', [
                'baseUrl'     => site_url('admin/billing/invoices'),
                'params'      => array_filter([
                    'q'      => $filters['q']      ?? '',
                    'status' => $filters['status'] ?? '',
                    'from'   => $filters['from']   ?? '',
                    'to'     => $filters['to']     ?? '',
                ], fn($v) => $v !== null && $v !== ''),
                'currentPage' => $page['currentPage'],
                'totalPages'  => $page['totalPages'],
                'totalCount'  => $page['total'],
                'perPage'     => $page['perPage'],
                'firstIndex'  => $page['firstIndex'],
                'lastIndex'   => $page['lastIndex'],
            ]) ?>
        <?php endif; ?>
    </div>
</div>
