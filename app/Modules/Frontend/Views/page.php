<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data); // heading, content, slug
?>
<section class="pt-32 pb-12 bg-transparent">
    <div class="mx-auto max-w-3xl px-6 lg:px-8 text-center" data-aos="fade-down">

        <h1 class="mt-6 text-4xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase"><?= esc($heading) ?></h1>
        <div class="w-16 h-1 bg-brand-500 mx-auto mt-6"></div>
    </div>
</section>

<section class="py-12 lg:py-16 bg-transparent relative isolate overflow-hidden" data-aos="fade-up" data-aos-delay="200">
    <?php if ($slug === 'about'): ?>
        <img src="<?= base_url('uploads/hero_nobg.png') ?>" alt="" class="absolute bottom-0 -right-24 md:right-0 h-[600px] md:h-[900px] w-auto opacity-10 grayscale pointer-events-none -z-10 object-contain">
    <?php endif; ?>

    <?php if ($slug === 'about'): ?>
        <div class="mx-auto max-w-5xl px-6 lg:px-8 relative z-10">
            <?= $content ?>
        </div>
    <?php else: ?>
        <div class="mx-auto max-w-3xl px-6 lg:px-8 relative z-10">
            <div class="bg-zinc-900/80 p-8 lg:p-12 border border-brand-500/20 shadow-2xl">
                <article class="prose prose-invert max-w-none font-sans font-light text-gray-300 leading-relaxed
                                prose-headings:font-display prose-headings:font-bold prose-headings:tracking-widest prose-headings:uppercase prose-headings:text-white prose-headings:mt-8
                                prose-h3:text-brand-400
                                prose-a:text-brand-400 hover:prose-a:text-white prose-a:transition-colors
                                prose-strong:font-bold prose-strong:text-white
                                prose-li:my-1">
                    <?= $content ?>
                </article>
            </div>

            <div class="mt-12 text-center">
                <a href="<?= site_url('/') ?>" class="inline-flex items-center gap-2 border border-white/20 bg-zinc-950 px-6 py-3 text-sm font-bold font-display uppercase tracking-widest text-white hover:bg-zinc-800 transition-colors">
                    <i data-lucide="arrow-left" class="size-4"></i> <?= lang('Site.confirm.back_home') ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</section>
