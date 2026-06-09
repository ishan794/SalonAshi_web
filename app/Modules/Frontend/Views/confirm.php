<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data);
$salonName = 'SALON ASHI';
$phone     = $s->get('biz_phone');
$startAt   = strtotime($appt['start_at']);
?>
<section class="pt-28 pb-16 bg-transparent min-h-screen">
    <div class="mx-auto max-w-2xl px-6 lg:px-8">

        <div class="text-center">
            <div class="mx-auto size-20 bg-brand-500 flex items-center justify-center text-zinc-950 shadow-2xl shadow-brand-500/20 border-4 border-zinc-900">
                <i data-lucide="check" class="size-10"></i>
            </div>
            <h1 class="mt-6 text-4xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase"><?= lang('Site.confirm.heading') ?></h1>
            <div class="w-16 h-1 bg-brand-500 mx-auto mt-6 mb-4"></div>
            <p class="mt-3 text-lg text-gray-400 font-light"><?= lang('Site.confirm.lead') ?></p>
        </div>

        <div class="mt-12 bg-zinc-900/80 p-8 lg:p-10 shadow-2xl border border-brand-500/20">
            <div class="flex items-center justify-between border-b border-brand-500/20 pb-6 mb-6">
                <div>
                    <p class="text-xs uppercase tracking-widest font-display text-gray-400"><?= lang('Site.confirm.reference') ?></p>
                    <p class="mt-1 text-2xl font-bold text-white font-display tracking-widest"><?= esc($appt['code']) ?></p>
                </div>
                <span class="inline-flex items-center gap-2 bg-brand-500/10 text-brand-400 border border-brand-500/30 px-3 py-1.5 text-xs font-bold font-display uppercase tracking-widest">
                    <span class="size-2 rounded-none bg-brand-500"></span> <?= lang('Site.confirm.pending') ?>
                </span>
            </div>

            <dl class="space-y-6 text-base font-sans text-gray-300">
                <div class="flex items-start gap-4">
                    <div class="flex size-10 bg-zinc-950 items-center justify-center text-brand-500 border border-white/5 shrink-0"><i data-lucide="calendar" class="size-5"></i></div>
                    <div class="pt-1">
                        <dt class="font-bold text-white tracking-wide"><?= esc(date('l, F j, Y', $startAt)) ?></dt>
                        <dd class="text-gray-400 text-sm mt-1"><?= esc(date('H:i', $startAt)) ?> &mdash; <?= lang('Site.confirm.duration', [(int)((strtotime($appt['end_at']) - $startAt) / 60)]) ?></dd>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="flex size-10 bg-zinc-950 items-center justify-center text-brand-500 border border-white/5 shrink-0"><i data-lucide="user-round" class="size-5"></i></div>
                    <div class="pt-2">
                        <dt class="font-bold text-white tracking-wide"><?= lang('Site.confirm.with', [esc($appt['staff_name'])]) ?></dt>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="flex size-10 bg-zinc-950 items-center justify-center text-brand-500 border border-white/5 shrink-0"><i data-lucide="sparkles" class="size-5"></i></div>
                    <div class="pt-1 w-full">
                        <dt class="font-bold text-white tracking-wide mb-2"><?= lang('Site.confirm.services') ?></dt>
                        <dd class="text-gray-400">
                            <ul class="space-y-2">
                                <?php foreach ($items as $it): ?>
                                    <li class="flex justify-between items-center border-b border-white/5 pb-2">
                                        <span><?= esc($it['service_name']) ?></span>
                                        <span class="text-white font-display tracking-widest">LKR <?= number_format((float)$it['price'], 0) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </dd>
                    </div>
                </div>
                <div class="flex items-start gap-4 border-t border-brand-500/20 pt-6">
                    <div class="flex size-10 bg-brand-500 items-center justify-center text-zinc-950 shrink-0 shadow-lg shadow-brand-500/20"><i data-lucide="receipt" class="size-5"></i></div>
                    <div class="pt-1 w-full flex justify-between items-end">
                        <div>
                            <dt class="font-bold text-white tracking-wide"><?= lang('Site.confirm.est_total') ?></dt>
                            <dd class="text-xs text-brand-400 font-display uppercase tracking-widest mt-1"><?= lang('Site.confirm.payable') ?></dd>
                        </div>
                        <dd class="text-brand-500 text-3xl font-bold font-display tracking-widest">LKR <?= number_format((float)$appt['subtotal'], 0) ?></dd>
                    </div>
                </div>
            </dl>
        </div>

        <div class="mt-8 bg-zinc-950 border-l-4 border-brand-500 p-6 shadow-xl text-sm text-gray-400">
            <p class="flex items-start gap-3"><i data-lucide="info" class="size-5 text-brand-500 shrink-0"></i>
                <span class="leading-relaxed"><?= lang('Site.confirm.sms_notice', ['<strong class="text-white font-sans">' . esc(phone_local($appt['customer_mobile'])) . '</strong>']) ?><?php if ($phone): ?> <?= lang('Site.confirm.reschedule', ['<a href="tel:' . esc(preg_replace('/[^0-9+]/', '', $phone)) . '" class="font-bold text-brand-400 hover:text-white transition-colors tracking-widest">' . esc($phone) . '</a>']) ?><?php endif; ?></span>
            </p>
        </div>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            <a href="<?= site_url('/') ?>" class="inline-flex items-center gap-2 border border-white/20 bg-zinc-950 px-6 py-3 text-sm font-bold font-display uppercase tracking-widest text-white hover:bg-zinc-800 transition-colors">
                <i data-lucide="home" class="size-4"></i> <?= lang('Site.confirm.back_home') ?>
            </a>
            <a href="<?= site_url('book') ?>" class="inline-flex items-center gap-2 bg-brand-500 px-6 py-3 text-sm font-bold font-display uppercase tracking-widest text-zinc-950 shadow-lg shadow-brand-500/20 hover:bg-brand-600 transition-colors">
                <i data-lucide="plus" class="size-4"></i> <?= lang('Site.confirm.book_another') ?>
            </a>
        </div>

        <!-- Leave a review prompt — appears after visit -->
        <div class="mt-12 bg-zinc-900/50 border border-brand-500/10 p-6 flex flex-col sm:flex-row items-center justify-between gap-6 hover:border-brand-500/30 transition-colors">
            <div class="flex items-center gap-4 min-w-0 text-center sm:text-left">
                <span class="hidden sm:flex size-12 items-center justify-center bg-brand-500/10 text-brand-400 shrink-0 border border-brand-500/20">
                    <i data-lucide="star" class="size-5"></i>
                </span>
                <p class="text-sm font-display tracking-widest uppercase text-gray-300"><?= lang('Site.confirm.review_cta_lead') ?></p>
            </div>
            <a href="<?= site_url('review/' . esc($appt['code'])) ?>" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 border border-brand-500 text-brand-400 px-5 py-2.5 text-xs font-bold font-display uppercase tracking-widest hover:bg-brand-500 hover:text-zinc-950 transition-colors shrink-0">
                <i data-lucide="pen-line" class="size-4"></i> <?= lang('Site.confirm.review_cta_btn') ?>
            </a>
        </div>
    </div>
</section>
