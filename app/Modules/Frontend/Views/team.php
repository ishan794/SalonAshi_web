<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data); // staff
?>
<section class="pt-32 pb-12 bg-transparent">
    <div class="mx-auto max-w-3xl px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 bg-brand-500/10 px-4 py-1.5 text-xs font-bold tracking-widest text-brand-400 border border-brand-500/20 font-display uppercase">
            <i data-lucide="users" class="size-4"></i> <?= lang('Site.team_page.heading') ?>
        </span>
        <h1 class="mt-6 text-4xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase"><?= lang('Site.team.heading') ?></h1>
        <div class="w-16 h-1 bg-brand-500 mx-auto mt-6 mb-4"></div>
        <p class="mt-3 text-lg text-gray-400 font-light"><?= lang('Site.team_page.lead') ?></p>
    </div>
</section>

<section class="py-12 lg:py-16 bg-transparent">
    <div class="mx-auto <?= defined("FRONTEND_CONTAINER_W") ? FRONTEND_CONTAINER_W : "max-w-7xl" ?> px-6 lg:px-8">
        <?php if (empty($staff)): ?>
            <p class="text-center text-gray-500 font-display uppercase tracking-widest">No team members to show yet.</p>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($staff as $st): ?>
                    <div class="group text-center bg-zinc-900/80 p-8 border border-brand-500/20 hover:border-brand-500 transition-colors shadow-2xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-brand-500/5 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out"></div>
                        <div class="relative z-10">
                            <div class="mx-auto size-28 bg-zinc-950 flex items-center justify-center text-brand-500 text-4xl font-bold font-display shadow-2xl border border-white/5 group-hover:bg-brand-500 group-hover:text-zinc-950 group-hover:border-transparent transition-colors">
                                <?= esc(strtoupper(substr($st['full_name'], 0, 1))) ?>
                            </div>
                            <p class="mt-6 font-bold text-lg font-display uppercase tracking-widest text-white group-hover:text-brand-400 transition-colors"><?= esc($st['full_name']) ?></p>
                            <p class="text-xs text-brand-500 font-display uppercase tracking-widest mt-1 mb-6"><?= esc($st['role'] ?: lang('Site.team.role')) ?></p>
                            <a href="<?= site_url('book') ?>" class="inline-flex items-center gap-2 border border-brand-500/30 bg-zinc-950 px-5 py-2 text-xs font-bold font-display uppercase tracking-widest text-white hover:bg-brand-500 hover:text-zinc-950 hover:border-brand-500 transition-colors">
                                <i data-lucide="calendar-plus" class="size-3.5"></i> <?= lang('Site.hero.book_btn') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
