<?php /** @var array $needed */ ?>
<div class="max-w-md mx-auto mt-12">
    <div class="rounded-xl bg-white dark:bg-gray-800 p-8 shadow-md ring-1 ring-gray-200 dark:ring-white/10 text-center">
        <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400">
            <i data-lucide="shield-x" class="size-8"></i>
        </div>
        <h2 class="mt-5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Permission denied</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Your role (<strong class="text-gray-900 dark:text-white"><?= esc(session('user.role_label') ?: session('user.role')) ?></strong>) doesn't have the permission needed for this action.</p>

        <div class="mt-5 rounded-md bg-gray-50 dark:bg-white/5 p-3 text-xs text-left">
            <p class="font-semibold text-gray-700 dark:text-gray-300">Required permission<?= count($needed) > 1 ? 's (any of)' : '' ?>:</p>
            <ul class="mt-1 space-y-0.5">
                <?php foreach ($needed as $n): ?>
                    <li class="font-mono text-brand-700 dark:text-brand-300"><?= esc($n) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="<?= site_url('admin/dashboard') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="home" class="size-4"></i> Back to dashboard
            </a>
            <a href="javascript:history.back()" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white">Go back</a>
        </div>
    </div>
</div>
