<?php /** @var \App\Modules\Settings\Models\SettingModel $s */ ?>
<section class="pt-32 pb-12 bg-gradient-to-br from-amber-50 to-white dark:from-gray-950 dark:to-gray-950 dark:bg-gray-950 dark:bg-none">
    <div class="mx-auto max-w-md px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/15 dark:bg-brand-500/20 px-3 py-1 text-xs font-semibold text-brand-700 dark:text-brand-300 ring-1 ring-brand-500/30">
            <i data-lucide="shield-check" class="size-3.5"></i> <?= lang('Site.portal.verify_badge') ?>
        </span>
        <h1 class="mt-4 text-3xl lg:text-4xl font-bold tracking-tight text-gray-900 dark:text-white font-display"><?= lang('Site.portal.verify_heading') ?></h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400"><?= lang('Site.portal.verify_lead') ?></p>
    </div>
</section>

<section class="py-10 lg:py-16 bg-white dark:bg-gray-950">
    <div class="mx-auto max-w-md px-6 lg:px-8">
        <?php if (session('flash_success')): ?>
            <div class="mb-5 rounded-lg bg-green-50 dark:bg-green-500/10 p-4 ring-1 ring-green-200 dark:ring-green-500/30 text-sm text-green-800 dark:text-green-300 flex items-start gap-2">
                <i data-lucide="check-circle-2" class="size-5 mt-0.5"></i>
                <span><?= esc(session('flash_success')) ?></span>
            </div>
        <?php endif; ?>
        <?php if (session('flash_error')): ?>
            <div class="mb-5 rounded-lg bg-red-50 dark:bg-red-500/10 p-4 ring-1 ring-red-200 dark:ring-red-500/30 text-sm text-red-800 dark:text-red-300 flex items-start gap-2">
                <i data-lucide="alert-triangle" class="size-5 mt-0.5"></i>
                <span><?= esc(session('flash_error')) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= site_url('portal/verify') ?>" class="rounded-2xl bg-white dark:bg-gray-900 p-6 lg:p-8 ring-1 ring-gray-200 dark:ring-white/10 shadow-sm space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white"><?= lang('Site.portal.code_label') ?></label>
                <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus autocomplete="one-time-code"
                       placeholder="123456"
                       class="mt-1 w-full text-center tracking-[0.5em] text-2xl font-mono rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?= lang('Site.portal.code_help') ?></p>
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-md bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                <i data-lucide="log-in" class="size-4"></i> <?= lang('Site.portal.verify_submit') ?>
            </button>
            <div class="text-center text-xs">
                <a href="<?= site_url('portal') ?>" class="text-gray-500 dark:text-gray-400 hover:text-brand-600"><?= lang('Site.portal.use_different_number') ?></a>
            </div>
        </form>
    </div>
</section>
