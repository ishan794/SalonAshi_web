<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Customers</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage customer profiles, history &amp; loyalty.</p>
        </div>
        <?php if (auth_has('customers.create')): ?>
        <a href="<?= site_url('admin/customers/create') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="plus" class="size-4"></i> New Customer
        </a>
        <?php endif; ?>
    </div>

    <!-- ── Membership summary cards (IMP_009) ── -->
    <?php
    $cards = [
        ['Total customers', (int)($stats['total'] ?? 0),    'users',  'brand'],
        ['Gold members',    (int)($stats['gold'] ?? 0),     'crown',  'amber'],
        ['Silver members',  (int)($stats['silver'] ?? 0),   'medal',  'slate'],
        ['No membership',   (int)($stats['none_count'] ?? 0),'user',  'gray'],
    ];
    ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php foreach ($cards as [$label, $val, $icon, $col]): ?>
            <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4 flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-lg bg-<?= $col ?>-100 dark:bg-<?= $col ?>-500/20 text-<?= $col ?>-600 dark:text-<?= $col ?>-300 shrink-0">
                    <i data-lucide="<?= $icon ?>" class="size-4"></i>
                </span>
                <div>
                    <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400"><?= esc($label) ?></p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white"><?= $val ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Search with live autocomplete (IMP_005/006) ── -->
    <form method="GET" class="max-w-xl flex items-center gap-2" x-data="customerSearch()" @click.outside="open=false">
        <div class="relative flex-1">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-2.5 size-4 text-gray-400 dark:text-gray-500"></i>
            <input name="q" x-model="q" value="<?= esc($q ?? '') ?>" autocomplete="off"
                   @input.debounce.250ms="fetchSuggest()" @focus="q.length>=2 && fetchSuggest()"
                   placeholder="Search name, mobile or email…"
                   class="w-full rounded-md border-gray-300 dark:border-white/10 pl-9 pr-9 text-sm focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 dark:bg-white/5 dark:text-white">
            <?php if (! empty($q)): ?>
                <a href="<?= site_url('admin/customers') ?>" title="Clear search" class="absolute right-2 top-1.5 inline-flex size-6 items-center justify-center rounded text-gray-400 hover:text-gray-700 dark:hover:text-white">
                    <i data-lucide="x" class="size-4"></i>
                </a>
            <?php endif; ?>

            <!-- Suggestions dropdown -->
            <div x-show="open && items.length" x-cloak
                 class="absolute z-30 mt-1 w-full rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-gray-900/5 dark:ring-white/10 overflow-hidden max-h-80 overflow-y-auto">
                <template x-for="it in items" :key="it.id">
                    <a :href="'<?= site_url('admin/customers/') ?>' + it.id"
                       class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5">
                        <span class="flex size-8 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20 text-brand-700 dark:text-brand-300 text-xs font-semibold shrink-0" x-text="it.name.charAt(0).toUpperCase()"></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white truncate" x-text="it.name"></span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate" x-text="[it.mobile, it.email].filter(Boolean).join(' · ')"></span>
                        </span>
                    </a>
                </template>
            </div>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700 shrink-0">
            <i data-lucide="search" class="size-4"></i> Search
        </button>
    </form>

    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Name</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Mobile</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Email</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Membership</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Status</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Loyalty</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5 bg-white dark:bg-gray-800">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No customers match.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): $blocked = ($r['status'] ?? 'active') === 'blocked'; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 <?= $blocked ? 'opacity-60' : '' ?>">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($r['full_name']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($r['mobile']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($r['email'] ?: '—') ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php
                            $colors = ['none' => 'gray', 'silver' => 'slate', 'gold' => 'amber', 'platinum' => 'purple'];
                            $c = $colors[$r['membership']] ?? 'gray';
                            ?>
                            <span class="inline-flex items-center rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 px-2 py-0.5 text-xs font-medium text-<?= $c ?>-700 dark:text-<?= $c ?>-300"><?= esc(ucfirst($r['membership'] ?: 'none')) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <?php if ($blocked): ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-500/20 px-2 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300"><i data-lucide="ban" class="size-3"></i> Blocked</span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/20 px-2 py-0.5 text-xs font-semibold text-green-700 dark:text-green-300"><span class="size-1.5 rounded-full bg-green-500"></span> Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= (int) $r['loyalty_points'] ?></td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            <a href="<?= site_url('admin/customers/' . $r['id']) ?>" class="text-brand-600 dark:text-brand-400 hover:text-brand-700">View</a>
                            <?php if (auth_has('customers.edit')): ?>
                                <a href="<?= site_url('admin/customers/' . $r['id'] . '/edit') ?>" class="ml-3 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Edit</a>
                                <form method="POST" action="<?= site_url('admin/customers/' . $r['id'] . '/block') ?>" class="inline" onsubmit="return confirm('<?= $blocked ? 'Unblock' : 'Block' ?> this customer?')">
                                    <?= csrf_field() ?>
                                    <button class="ml-3 <?= $blocked ? 'text-green-600 dark:text-green-400 hover:text-green-700' : 'text-amber-600 dark:text-amber-400 hover:text-amber-700' ?>"><?= $blocked ? 'Unblock' : 'Block' ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if (auth_has('customers.delete')): ?>
                                <form method="POST" action="<?= site_url('admin/customers/' . $r['id']) ?>" class="inline" onsubmit="return confirm('Permanently delete <?= esc($r['full_name'], 'js') ?>? This cannot be undone.')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="ml-3 text-red-600 dark:text-red-400 hover:text-red-700">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ── Pager ── -->
        <?php if (($totalPages ?? 1) > 0 && ($totalCount ?? 0) > 0):
            // Build a compact page-number list (1, …, current-1, current, current+1, …, last)
            $cur = max(1, (int)($currentPage ?? 1));
            $tp  = max(1, (int)($totalPages ?? 1));
            $window = [];
            for ($i = max(1, $cur - 2); $i <= min($tp, $cur + 2); $i++) $window[] = $i;
            if ($window && $window[0] > 2)      array_unshift($window, 1, '…');
            elseif ($window && $window[0] === 2) array_unshift($window, 1);
            $last = end($window); reset($window);
            if ($last < $tp - 1) { $window[] = '…'; $window[] = $tp; }
            elseif ($last === $tp - 1) $window[] = $tp;

            // Preserve existing query params (q, per_page) when building page links
            $qs = function(int $p) use ($q, $perPage): string {
                $params = ['page' => $p];
                if ($q)        $params['q']        = $q;
                if (! empty($perPage) && $perPage !== 20) $params['per_page'] = $perPage;
                return site_url('admin/customers') . '?' . http_build_query($params);
            };
        ?>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 dark:border-white/10 px-4 py-3 bg-gray-50 dark:bg-white/5">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Showing <strong><?= (int)($firstIndex ?? 0) ?></strong>–<strong><?= (int)($lastIndex ?? 0) ?></strong> of <strong><?= (int)($totalCount ?? 0) ?></strong>
                </p>
                <div class="flex items-center gap-2">
                    <!-- Per-page selector -->
                    <form method="GET" class="inline-flex items-center gap-1.5 text-xs">
                        <?php if ($q): ?><input type="hidden" name="q" value="<?= esc($q) ?>"><?php endif; ?>
                        <label for="per_page" class="text-gray-500 dark:text-gray-400">Per page</label>
                        <select id="per_page" name="per_page" onchange="this.form.submit()" class="rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-xs py-1">
                            <?php foreach ([10, 20, 50, 100] as $size): ?>
                                <option value="<?= $size ?>" <?= (int)$perPage === $size ? 'selected' : '' ?>><?= $size ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <!-- Page nav -->
                    <?php if ($tp > 1): ?>
                        <nav class="inline-flex items-center gap-1" aria-label="Pagination">
                            <a href="<?= $cur > 1 ? esc($qs($cur - 1)) : '#' ?>"
                               class="inline-flex size-7 items-center justify-center rounded-md text-xs font-medium <?= $cur > 1 ? 'text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 ring-1 ring-gray-300 dark:ring-white/10' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' ?>"
                               aria-label="Previous page" <?= $cur <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                                <i data-lucide="chevron-left" class="size-3.5"></i>
                            </a>
                            <?php foreach ($window as $p): ?>
                                <?php if ($p === '…'): ?>
                                    <span class="px-1.5 text-xs text-gray-400 dark:text-gray-500">…</span>
                                <?php elseif ((int)$p === $cur): ?>
                                    <span class="inline-flex min-w-[28px] h-7 items-center justify-center rounded-md bg-brand-600 px-2 text-xs font-semibold text-white"><?= (int)$p ?></span>
                                <?php else: ?>
                                    <a href="<?= esc($qs((int)$p)) ?>" class="inline-flex min-w-[28px] h-7 items-center justify-center rounded-md px-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 ring-1 ring-gray-300 dark:ring-white/10"><?= (int)$p ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <a href="<?= $cur < $tp ? esc($qs($cur + 1)) : '#' ?>"
                               class="inline-flex size-7 items-center justify-center rounded-md text-xs font-medium <?= $cur < $tp ? 'text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 ring-1 ring-gray-300 dark:ring-white/10' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' ?>"
                               aria-label="Next page" <?= $cur >= $tp ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                                <i data-lucide="chevron-right" class="size-3.5"></i>
                            </a>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function customerSearch() {
    return {
        q: <?= json_encode($q ?? '') ?>,
        items: [],
        open: false,
        async fetchSuggest() {
            if (this.q.length < 2) { this.items = []; this.open = false; return; }
            try {
                const r = await fetch('<?= site_url('admin/customers/suggest') ?>?q=' + encodeURIComponent(this.q), { credentials: 'same-origin' });
                if (r.ok) { this.items = await r.json(); this.open = this.items.length > 0; }
            } catch (e) {}
        }
    };
}
</script>
