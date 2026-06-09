<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data); // appt, code, hasReviewed
?>
<section class="pt-32 pb-12 bg-transparent">
    <div class="mx-auto max-w-2xl px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 bg-brand-500/10 px-4 py-1.5 text-xs font-bold tracking-widest text-brand-400 border border-brand-500/20 font-display uppercase">
            <i data-lucide="star" class="size-4"></i> <?= lang('Site.review.badge') ?>
        </span>
        <h1 class="mt-6 text-4xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase"><?= lang('Site.review.heading') ?></h1>
        <div class="w-16 h-1 bg-brand-500 mx-auto mt-6 mb-4"></div>
        <p class="mt-3 text-lg text-gray-400 font-light"><?= lang('Site.review.lead') ?></p>
    </div>
</section>

<section class="py-10 lg:py-16 bg-transparent">
    <div class="mx-auto max-w-2xl px-6 lg:px-8">
        <?php if (session('flash_success')): ?>
            <div class="mb-6 bg-brand-500/10 border border-brand-500 p-4 text-sm text-brand-400 flex items-start gap-3 shadow-xl font-display uppercase tracking-widest">
                <i data-lucide="check-circle-2" class="size-5 mt-0.5"></i>
                <span><?= esc(session('flash_success')) ?></span>
            </div>
        <?php endif; ?>
        <?php if (session('flash_error')): ?>
            <div class="mb-6 bg-red-900/20 border border-red-500 p-4 text-sm text-red-400 flex items-start gap-3 shadow-xl font-display uppercase tracking-widest">
                <i data-lucide="alert-triangle" class="size-5 mt-0.5"></i>
                <span><?= esc(session('flash_error')) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($hasReviewed): ?>
            <div class="bg-zinc-900/80 p-10 border border-brand-500/20 shadow-2xl text-center">
                <i data-lucide="check-circle-2" class="size-16 text-brand-500 mx-auto"></i>
                <p class="mt-6 text-xl font-bold text-white font-display uppercase tracking-widest"><?= lang('Site.review.already_done') ?></p>
                <a href="<?= site_url('/') ?>" class="mt-6 inline-flex items-center gap-2 text-sm font-bold font-display uppercase tracking-widest text-brand-400 hover:text-white transition-colors">
                    <i data-lucide="arrow-left" class="size-4"></i> <?= lang('Site.confirm.back_home') ?>
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= site_url('review') ?>" class="bg-zinc-900/80 p-8 lg:p-10 border border-brand-500/20 shadow-2xl space-y-8"
                  x-data="{ rating: 0 }">
                <?= csrf_field() ?>
                <?php if ($code): ?>
                    <input type="hidden" name="appointment_code" value="<?= esc($code) ?>">
                    <?php if ($appt): ?>
                        <div class="bg-zinc-950 border border-white/10 p-4 text-sm font-display tracking-widest uppercase text-brand-400">
                            <?= lang('Site.review.linked', [esc($appt['code'])]) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Star rating -->
                <div>
                    <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-3"><?= lang('Site.review.your_rating') ?> <span class="text-brand-500">*</span></label>
                    <div class="mt-2 flex items-center gap-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" @click="rating = <?= $i ?>" @mouseenter="rating = <?= $i ?>"
                                    class="p-1 transition-transform hover:scale-110 focus:outline-none" :class="rating >= <?= $i ?> ? 'text-brand-500' : 'text-zinc-700'">
                                <i data-lucide="star" class="size-10" :class="rating >= <?= $i ?> ? 'fill-brand-500' : ''"></i>
                            </button>
                        <?php endfor; ?>
                        <span class="ml-4 text-base font-bold font-display uppercase tracking-widest text-brand-400" x-text="rating ? rating + ' / 5' : ''"></span>
                    </div>
                    <input type="hidden" name="rating" :value="rating" required>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.review.your_name') ?> <span class="text-brand-500">*</span></label>
                        <input name="name" required class="w-full bg-zinc-950 border border-white/10 text-white py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans">
                    </div>
                    <div>
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.review.title_opt') ?></label>
                        <input name="title" placeholder="<?= esc(lang('Site.review.title_ph')) ?>" class="w-full bg-zinc-950 border border-white/10 text-white py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans placeholder-gray-600">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.review.message') ?> <span class="text-brand-500">*</span></label>
                    <textarea name="body" rows="6" required placeholder="<?= esc(lang('Site.review.message_ph')) ?>" class="w-full bg-zinc-950 border border-white/10 text-white py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans placeholder-gray-600"></textarea>
                </div>

                <div class="flex justify-end pt-6 border-t border-brand-500/10">
                    <button type="submit" :disabled="!rating" class="inline-flex items-center gap-3 bg-brand-500 px-8 py-4 text-sm font-bold font-display uppercase tracking-widest text-zinc-950 hover:bg-brand-600 transition-colors shadow-lg shadow-brand-500/20 disabled:bg-zinc-800 disabled:text-gray-500 disabled:cursor-not-allowed disabled:shadow-none border border-transparent disabled:border-white/10">
                        <i data-lucide="send" class="size-4"></i> <?= lang('Site.review.submit') ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>
