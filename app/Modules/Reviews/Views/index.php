<?php
/** @var array $rows, $summary; @var ?string $status, $source; @var ?int $minRating */
$statusColors = ['pending' => 'amber', 'approved' => 'green', 'rejected' => 'gray'];
$sourceIcons  = ['in-app' => 'message-square', 'google' => 'badge-check', 'manual' => 'user-plus'];
?>
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Reviews</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Customer ratings, in-app feedback &amp; imported Google reviews.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="<?= site_url('admin/reviews/import-google') ?>" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-white dark:bg-gray-800 ring-1 ring-gray-300 dark:ring-white/10 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5">
                    <i data-lucide="refresh-cw" class="size-4"></i> Import from Google
                </button>
            </form>
            <a href="<?= site_url('admin/reviews/create') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="plus" class="size-4"></i> Add review
            </a>
        </div>
    </div>

    <!-- Summary KPIs -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4">
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Average rating</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-1">
                <?= number_format((float)$summary['avg_rating'], 1) ?>
                <i data-lucide="star" class="size-5 text-amber-400 fill-amber-400"></i>
            </p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4">
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Total reviews</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"><?= (int)$summary['total'] ?></p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4">
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Pending</p>
            <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400"><?= (int)$summary['pending_count'] ?></p>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-4">
            <p class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Approved</p>
            <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"><?= (int)$summary['approved_count'] ?></p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap items-end gap-3 bg-white dark:bg-gray-800 p-4 rounded-lg shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Status</label>
            <select name="status" class="mt-1 rounded-md border-gray-300 dark:border-white/10 text-sm dark:bg-white/5 dark:text-white">
                <option value="">All</option>
                <option value="pending"  <?= $status==='pending'?'selected':'' ?>>Pending</option>
                <option value="approved" <?= $status==='approved'?'selected':'' ?>>Approved</option>
                <option value="rejected" <?= $status==='rejected'?'selected':'' ?>>Rejected</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Source</label>
            <select name="source" class="mt-1 rounded-md border-gray-300 dark:border-white/10 text-sm dark:bg-white/5 dark:text-white">
                <option value="">All</option>
                <option value="in-app" <?= $source==='in-app'?'selected':'' ?>>In-app</option>
                <option value="google" <?= $source==='google'?'selected':'' ?>>Google</option>
                <option value="manual" <?= $source==='manual'?'selected':'' ?>>Manual</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Min rating</label>
            <select name="min_rating" class="mt-1 rounded-md border-gray-300 dark:border-white/10 text-sm dark:bg-white/5 dark:text-white">
                <option value="">Any</option>
                <?php for ($i=5; $i>=1; $i--): ?>
                    <option value="<?= $i ?>" <?= $minRating === $i ? 'selected' : '' ?>><?= $i ?>+ stars</option>
                <?php endfor; ?>
            </select>
        </div>
        <button class="rounded-md bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700">Apply</button>
        <a href="<?= site_url('admin/reviews') ?>" class="rounded-md bg-gray-100 dark:bg-white/5 px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Reset</a>
    </form>

    <!-- Reviews list -->
    <?php if (empty($rows)): ?>
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-8 text-center text-gray-500 dark:text-gray-400">No reviews match the current filters.</div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($rows as $r):
                $c = $statusColors[$r['status']] ?? 'gray';
                $icon = $sourceIcons[$r['source']] ?? 'message-square';
            ?>
                <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-5 flex items-start gap-4">
                    <!-- Avatar -->
                    <div class="size-12 rounded-full bg-gradient-to-br from-brand-400 to-amber-600 flex items-center justify-center text-white font-semibold shrink-0">
                        <?php if (! empty($r['reviewer_avatar_url'])): ?>
                            <img src="<?= esc($r['reviewer_avatar_url']) ?>" alt="" class="size-12 rounded-full object-cover">
                        <?php else: ?>
                            <?= esc(strtoupper(substr($r['reviewer_name'], 0, 1))) ?>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold text-gray-900 dark:text-white"><?= esc($r['reviewer_name']) ?></p>
                            <span class="inline-flex items-center gap-0.5 text-amber-400">
                                <?php for ($i=1; $i<=5; $i++): ?>
                                    <i data-lucide="star" class="size-3.5 <?= $i <= $r['rating'] ? 'fill-amber-400' : 'opacity-30' ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 text-<?= $c ?>-700 dark:text-<?= $c ?>-300 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide"><?= esc($r['status']) ?></span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 px-2 py-0.5 text-xs"><i data-lucide="<?= esc($icon) ?>" class="size-3"></i> <?= esc($r['source']) ?></span>
                            <?php if (! empty($r['is_featured'])): ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-brand-100 dark:bg-brand-500/20 text-brand-700 dark:text-brand-300 px-2 py-0.5 text-xs font-semibold"><i data-lucide="star" class="size-3"></i> Featured</span>
                            <?php endif; ?>
                        </div>
                        <?php if (! empty($r['title'])): ?>
                            <p class="mt-2 font-semibold text-gray-900 dark:text-white"><?= esc($r['title']) ?></p>
                        <?php endif; ?>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"><?= esc($r['body']) ?></p>
                        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                            <?= esc(date('M j, Y H:i', strtotime($r['source_created_at'] ?: $r['created_at']))) ?>
                            <?php if (! empty($r['source_url'])): ?> · <a href="<?= esc($r['source_url']) ?>" target="_blank" class="text-brand-600 dark:text-brand-400 hover:underline">View source</a><?php endif; ?>
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        <?php if ($r['status'] !== 'approved'): ?>
                            <form method="POST" action="<?= site_url('admin/reviews/' . $r['id'] . '/approve') ?>" class="inline">
                                <?= csrf_field() ?>
                                <button class="inline-flex items-center gap-1 rounded-md bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700"><i data-lucide="check" class="size-3"></i> Approve</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($r['status'] !== 'rejected'): ?>
                            <form method="POST" action="<?= site_url('admin/reviews/' . $r['id'] . '/reject') ?>" class="inline">
                                <?= csrf_field() ?>
                                <button class="inline-flex items-center gap-1 rounded-md bg-gray-200 dark:bg-white/10 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-300"><i data-lucide="x" class="size-3"></i> Reject</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="<?= site_url('admin/reviews/' . $r['id'] . '/featured') ?>" class="inline">
                            <?= csrf_field() ?>
                            <button class="inline-flex items-center gap-1 rounded-md bg-brand-100 dark:bg-brand-500/20 text-brand-700 dark:text-brand-300 px-2.5 py-1 text-xs font-semibold hover:bg-brand-500 hover:text-white">
                                <i data-lucide="star" class="size-3"></i> <?= empty($r['is_featured']) ? 'Feature' : 'Unfeature' ?>
                            </button>
                        </form>
                        <form method="POST" action="<?= site_url('admin/reviews/' . $r['id']) ?>" class="inline" onsubmit="return confirm('Delete this review?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="inline-flex items-center gap-1 rounded-md bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 px-2.5 py-1 text-xs font-semibold hover:bg-red-600 hover:text-white"><i data-lucide="trash-2" class="size-3"></i> Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
