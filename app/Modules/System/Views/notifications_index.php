<?php /** @var array $rows; @var string $filter */ ?>
<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Notifications</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Everything that needs your attention — new bookings, payments, reviews, system events.</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="inline-flex rounded-md ring-1 ring-gray-300 dark:ring-white/10 overflow-hidden">
                <?php foreach (['all' => 'All','unread' => 'Unread','read' => 'Read'] as $key => $label): ?>
                    <a href="?filter=<?= $key ?>" class="<?= $filter === $key ? 'bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5' ?> px-3 py-1.5 text-xs font-semibold"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="<?= site_url('admin/notifications/mark-all-read') ?>" class="inline">
                <?= csrf_field() ?>
                <button class="inline-flex items-center gap-1.5 rounded-md bg-white dark:bg-gray-800 ring-1 ring-gray-300 dark:ring-white/10 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                    <i data-lucide="check-check" class="size-3.5"></i> Mark all read
                </button>
            </form>
        </div>
    </div>

    <?php if (empty($rows)): ?>
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 p-10 text-center">
            <i data-lucide="bell-off" class="size-10 text-gray-300 mx-auto"></i>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Nothing to show. Notifications will appear here as activity happens.</p>
        </div>
    <?php else: ?>
        <div class="rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                <?php foreach ($rows as $n): $c = $n['color'] ?: 'gray'; ?>
                    <li class="<?= $n['is_read'] ? '' : 'bg-brand-50/30 dark:bg-brand-500/5' ?> px-4 py-3 flex items-start gap-3">
                        <span class="flex size-9 items-center justify-center rounded-full bg-<?= esc($c) ?>-100 dark:bg-<?= esc($c) ?>-500/20 text-<?= esc($c) ?>-600 dark:text-<?= esc($c) ?>-300 shrink-0">
                            <i data-lucide="<?= esc($n['icon'] ?: 'bell') ?>" class="size-4"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?= esc($n['title']) ?></p>
                                <span class="text-[11px] text-gray-400 dark:text-gray-500 shrink-0"><?= esc(date('M j, H:i', strtotime($n['created_at']))) ?></span>
                            </div>
                            <?php if ($n['body']): ?><p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400"><?= esc($n['body']) ?></p><?php endif; ?>
                            <div class="mt-1.5 flex items-center gap-3 text-xs">
                                <?php if ($n['link']): ?><a href="<?= esc($n['link']) ?>" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">Open →</a><?php endif; ?>
                                <?php if (! $n['is_read']): ?>
                                    <form method="POST" action="<?= site_url('admin/notifications/' . (int)$n['id'] . '/read') ?>" class="inline"><?= csrf_field() ?><button class="text-gray-500 dark:text-gray-400 hover:text-brand-600">Mark read</button></form>
                                <?php endif; ?>
                                <form method="POST" action="<?= site_url('admin/notifications/' . (int)$n['id']) ?>" class="inline" onsubmit="return confirm('Delete this notification?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="text-gray-400 hover:text-red-600">Delete</button>
                                </form>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
