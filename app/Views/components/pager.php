<?php
/**
 * Reusable pagination bar.
 *
 * @var string $baseUrl       e.g. site_url('admin/billing/invoices')
 * @var array  $params        existing query params to preserve (q, status, from, to…)
 * @var int    $currentPage
 * @var int    $totalPages
 * @var int    $totalCount
 * @var int    $perPage
 * @var int    $firstIndex
 * @var int    $lastIndex
 * @var array  $perPageOptions  optional, defaults [20,50,100]
 */
$perPageOptions = $perPageOptions ?? [20, 50, 100];
if (($totalCount ?? 0) <= 0) return;

$cur = max(1, (int) $currentPage);
$tp  = max(1, (int) $totalPages);

// Compact page-number window: 1 … cur-2..cur+2 … last
$window = [];
for ($i = max(1, $cur - 2); $i <= min($tp, $cur + 2); $i++) $window[] = $i;
if ($window && $window[0] > 2)        array_unshift($window, 1, '…');
elseif ($window && $window[0] === 2)  array_unshift($window, 1);
$lastWin = end($window); reset($window);
if ($lastWin < $tp - 1)      { $window[] = '…'; $window[] = $tp; }
elseif ($lastWin === $tp - 1) $window[] = $tp;

$link = function (int $p) use ($baseUrl, $params): string {
    $qs = array_filter($params, fn($v) => $v !== null && $v !== '');
    $qs['page'] = $p;
    return esc($baseUrl . '?' . http_build_query($qs));
};
?>
<div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 dark:border-white/10 px-4 py-3 bg-gray-50 dark:bg-white/5">
    <p class="text-xs text-gray-600 dark:text-gray-400">
        Showing <strong><?= (int)$firstIndex ?></strong>–<strong><?= (int)$lastIndex ?></strong> of <strong><?= (int)$totalCount ?></strong>
    </p>
    <div class="flex items-center gap-2">
        <!-- Per-page -->
        <form method="GET" class="inline-flex items-center gap-1.5 text-xs">
            <?php foreach (array_filter($params, fn($v) => $v !== null && $v !== '') as $k => $v): if ($k === 'per_page' || $k === 'page') continue; ?>
                <input type="hidden" name="<?= esc($k) ?>" value="<?= esc($v) ?>">
            <?php endforeach; ?>
            <label class="text-gray-500 dark:text-gray-400">Per page</label>
            <select name="per_page" onchange="this.form.submit()" class="rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-xs py-1">
                <?php foreach ($perPageOptions as $size): ?>
                    <option value="<?= $size ?>" <?= (int)$perPage === $size ? 'selected' : '' ?>><?= $size ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($tp > 1): ?>
            <nav class="inline-flex items-center gap-1" aria-label="Pagination">
                <a href="<?= $cur > 1 ? $link($cur - 1) : '#' ?>"
                   class="inline-flex size-7 items-center justify-center rounded-md text-xs <?= $cur > 1 ? 'text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 ring-1 ring-gray-300 dark:ring-white/10' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' ?>"
                   <?= $cur <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i data-lucide="chevron-left" class="size-3.5"></i></a>
                <?php foreach ($window as $p): ?>
                    <?php if ($p === '…'): ?>
                        <span class="px-1.5 text-xs text-gray-400 dark:text-gray-500">…</span>
                    <?php elseif ((int)$p === $cur): ?>
                        <span class="inline-flex min-w-[28px] h-7 items-center justify-center rounded-md bg-brand-600 px-2 text-xs font-semibold text-white"><?= (int)$p ?></span>
                    <?php else: ?>
                        <a href="<?= $link((int)$p) ?>" class="inline-flex min-w-[28px] h-7 items-center justify-center rounded-md px-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 ring-1 ring-gray-300 dark:ring-white/10"><?= (int)$p ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <a href="<?= $cur < $tp ? $link($cur + 1) : '#' ?>"
                   class="inline-flex size-7 items-center justify-center rounded-md text-xs <?= $cur < $tp ? 'text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 ring-1 ring-gray-300 dark:ring-white/10' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' ?>"
                   <?= $cur >= $tp ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i data-lucide="chevron-right" class="size-3.5"></i></a>
            </nav>
        <?php endif; ?>
    </div>
</div>
