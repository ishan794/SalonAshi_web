<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Services</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage the salon's service menu.</p>
        </div>
        <a href="<?= site_url('admin/services/create') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="plus" class="size-4"></i> New Service
        </a>
    </div>

    <!-- Search Bar -->
    <div class="flex items-center justify-between gap-4 flex-wrap bg-white dark:bg-gray-800 p-4 rounded-lg shadow ring-1 ring-gray-200 dark:ring-white/10">
        <form method="GET" action="<?= site_url('admin/services') ?>" class="flex-1 max-w-md flex gap-2">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i data-lucide="search" class="size-4 text-gray-400"></i>
                </div>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Search services or categories..." class="block w-full rounded-md border-gray-300 dark:border-white/10 pl-9 pr-3 py-1.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-brand-500 dark:bg-white/5">
            </div>
            <button type="submit" class="rounded-md bg-brand-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-700">Search</button>
            <?php if (!empty($q)): ?>
                <a href="<?= site_url('admin/services') ?>" class="rounded-md border border-gray-300 dark:border-white/10 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 flex items-center">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Service</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Category</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Duration</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Price</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Active</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5 bg-white dark:bg-gray-800">
                <?php foreach ($rows as $r): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($r['name']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($r['category_name'] ?: '—') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= (int) $r['duration_min'] ?> min</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">LKR <?= number_format((float) $r['price'], 2) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php if ($r['is_active']): ?>
                                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-500/20 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">Active</span>
                            <?php else: ?>
                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="<?= site_url('admin/services/'.$r['id'].'/edit') ?>" class="text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300">Edit</a>
                            <form method="POST" action="<?= site_url('admin/services/'.$r['id']) ?>" class="inline" onsubmit="return confirm('Delete this service?');">
                                <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                <button class="ml-3 text-red-600 dark:text-red-400 hover:text-red-700 dark:text-red-300">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
        <div class="flex items-center justify-between border-t border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6 mt-4 rounded-lg shadow">
            <div class="flex flex-1 justify-between sm:hidden">
                <?php if ($pager->getPreviousPageURI()): ?>
                    <a href="<?= $pager->getPreviousPageURI() ?>" class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5">Previous</a>
                <?php else: ?>
                    <span class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-white/10 bg-gray-100 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">Previous</span>
                <?php endif; ?>
                
                <?php if ($pager->getNextPageURI()): ?>
                    <a href="<?= $pager->getNextPageURI() ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5">Next</a>
                <?php else: ?>
                    <span class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-white/10 bg-gray-100 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">Next</span>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Showing page <span class="font-medium"><?= $pager->getCurrentPage() ?></span> of <span class="font-medium"><?= $pager->getPageCount() ?></span> pages.
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <?php if ($pager->getPreviousPageURI()): ?>
                            <a href="<?= $pager->getPreviousPageURI() ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Previous</span>
                                <i data-lucide="chevron-left" class="size-5"></i>
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 dark:text-gray-600 ring-1 ring-inset ring-gray-300 dark:ring-white/10 cursor-not-allowed">
                                <span class="sr-only">Previous</span>
                                <i data-lucide="chevron-left" class="size-5"></i>
                            </span>
                        <?php endif; ?>

                        <?php 
                        $currentPage = $pager->getCurrentPage();
                        $pageCount = $pager->getPageCount();
                        $start = max(1, $currentPage - 2);
                        $end = min($pageCount, $currentPage + 2);
                        
                        if ($start > 1): ?>
                            <a href="<?= $pager->getPageURI(1) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $currentPage == 1 ? 'z-10 bg-brand-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600' : 'text-gray-900 dark:text-gray-200 ring-1 ring-inset ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 focus:z-20 focus:outline-offset-0' ?>">1</a>
                            <?php if ($start > 2): ?>
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-white/10">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($p = $start; $p <= $end; $p++): ?>
                            <a href="<?= $pager->getPageURI($p) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $currentPage == $p ? 'z-10 bg-brand-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600' : 'text-gray-900 dark:text-gray-200 ring-1 ring-inset ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 focus:z-20 focus:outline-offset-0' ?>"><?= $p ?></a>
                        <?php endfor; ?>

                        <?php if ($end < $pageCount): ?>
                            <?php if ($end < $pageCount - 1): ?>
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-white/10">...</span>
                            <?php endif; ?>
                            <a href="<?= $pager->getPageURI($pageCount) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $currentPage == $pageCount ? 'z-10 bg-brand-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600' : 'text-gray-900 dark:text-gray-200 ring-1 ring-inset ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 focus:z-20 focus:outline-offset-0' ?>"><?= $pageCount ?></a>
                        <?php endif; ?>

                        <?php if ($pager->getNextPageURI()): ?>
                            <a href="<?= $pager->getNextPageURI() ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Next</span>
                                <i data-lucide="chevron-right" class="size-5"></i>
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 dark:text-gray-600 ring-1 ring-inset ring-gray-300 dark:ring-white/10 cursor-not-allowed">
                                <span class="sr-only">Next</span>
                                <i data-lucide="chevron-right" class="size-5"></i>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
