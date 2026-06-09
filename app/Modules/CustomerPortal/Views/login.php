<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data);
?>
<section class="pt-28 pb-16 bg-transparent min-h-screen">
    <div class="mx-auto max-w-lg px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-down">
            <h1 class="mt-6 text-4xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase"><?= lang('Site.portal.login_heading') ?></h1>
            <div class="w-16 h-1 bg-brand-500 mx-auto mt-6 mb-4"></div>
            <p class="mt-3 text-gray-400 font-light"><?= lang('Site.portal.login_lead') ?></p>
        </div>

        <?php if (session('flash_error')): ?>
            <div class="mb-8 bg-zinc-900 border-l-4 border-red-500 p-4 text-sm font-display tracking-widest uppercase text-red-400 flex items-center gap-3" data-aos="fade-up" data-aos-delay="100">
                <i data-lucide="alert-triangle" class="size-5"></i>
                <span><?= esc(session('flash_error')) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= site_url('portal/request-otp') ?>" class="bg-zinc-900/80 p-8 border border-brand-500/20 shadow-2xl space-y-6" data-aos="fade-up" data-aos-delay="200">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.portal.mobile_label') ?></label>
                <input name="email" type="email" required autofocus value="<?= esc($email ?? '') ?>"
                       placeholder="you@example.com"
                       class="w-full bg-zinc-950 border border-white/10 text-white text-base py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans">
                <p class="mt-2 text-xs font-display tracking-widest uppercase text-gray-500"><?= lang('Site.portal.mobile_help') ?></p>
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-brand-500 px-6 py-4 text-lg font-bold font-display uppercase tracking-widest text-zinc-950 hover:bg-white transition-colors shadow-lg shadow-brand-500/20">
                <?= lang('Site.portal.send_code') ?> <i data-lucide="send" class="size-4"></i>
            </button>
            <p class="text-xs text-center font-display tracking-widest uppercase text-gray-500 pt-4 border-t border-brand-500/10">
                <?= lang('Site.portal.no_account') ?> <a href="<?= site_url('book') ?>" class="text-brand-400 hover:text-white transition-colors"><?= lang('Site.hero.book_btn') ?></a>
            </p>
        </form>
    </div>
</section>
