<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data);
?>
<section class="pt-32 pb-16 bg-transparent">
    <div class="mx-auto <?= defined("FRONTEND_CONTAINER_W") ? FRONTEND_CONTAINER_W : "max-w-7xl" ?> px-6 lg:px-8 text-center" data-aos="fade-down">
        <div class="max-w-2xl mx-auto">
<h1 class="text-4xl lg:text-6xl font-bold tracking-widest text-white font-display uppercase"><?= lang('Site.services_page.heading') ?></h1>
            <div class="w-16 h-1 bg-brand-500 mx-auto mt-6 mb-4"></div>
            <p class="mt-3 text-lg text-gray-400 font-light"><?= lang('Site.services_page.lead') ?></p>
        </div>
    </div>
</section>

<section class="py-16 bg-transparent relative isolate overflow-hidden">
    <!-- Original Decorative Tools Watermark -->
    <img src="<?= base_url('uploads/tools-bg.jpg') ?>" alt="" class="absolute top-0 right-0 w-full h-full object-contain object-right opacity-5 grayscale invert mix-blend-screen pointer-events-none -z-10">
    
    <!-- Top Creative Scissors Watermark -->
    <img src="<?= base_url('uploads/scissors-bg.jpg') ?>" alt="" class="absolute -top-32 -left-32 sm:-top-48 sm:-left-48 w-[500px] h-[500px] lg:w-[800px] lg:h-[800px] object-contain opacity-10 grayscale invert mix-blend-screen pointer-events-none -z-10 transform -rotate-12 origin-center">
    
    <!-- Bottom Creative Scissors Watermark -->
    <img src="<?= base_url('uploads/scissors-bg.jpg') ?>" alt="" class="absolute -bottom-32 -right-32 sm:-bottom-48 sm:-right-48 w-[500px] h-[500px] lg:w-[800px] lg:h-[800px] object-contain opacity-10 grayscale invert mix-blend-screen pointer-events-none -z-10 transform rotate-[160deg] origin-center">
    
    <div class="mx-auto max-w-5xl px-6 lg:px-8 space-y-16 relative z-10">
        <?php foreach ($categories as $cat): if (empty($byCategory[(int)$cat['id']])) continue; ?>
            <div data-aos="fade-up">
                <h2 class="text-3xl font-bold text-brand-400 font-display uppercase tracking-widest mb-8 flex items-center gap-4">
                    <?= esc($cat['name']) ?>
                    <span class="flex-1 h-px bg-brand-500/20"></span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($byCategory[(int)$cat['id']] as $i => $svc): ?>
                        <div class="group bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 hover:border-brand-500 transition-all duration-300 shadow-xl flex flex-col justify-between h-full relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex flex-col sm:flex-row justify-between items-start gap-2 sm:gap-4 mb-4">
                                    <h3 class="text-xl sm:text-2xl font-bold font-display uppercase tracking-widest text-white group-hover:text-brand-400 transition-colors leading-tight"><?= esc($svc['name']) ?></h3>
                                    <span class="text-xl sm:text-2xl font-bold font-display tracking-widest text-brand-500 shrink-0">LKR <?= number_format((float)$svc['price'], 0) ?></span>
                                </div>
                                <p class="text-sm font-display uppercase tracking-wider text-gray-500 mb-5">
                                    <i data-lucide="clock" class="size-4 inline mb-0.5"></i> <?= (int)$svc['duration_min'] ?> <?= lang('Site.services_page.minutes') ?>
                                </p>
                                
                                <div class="text-base font-sans text-gray-400 leading-relaxed font-light mb-8">
                                    <?php if ($svc['description']): ?>
                                        <?= esc($svc['description']) ?>
                                    <?php else: ?>
                                        Experience our premium service designed specifically for you. We ensure precision, style, and a flawless finish using high-quality products.
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-auto relative z-10 pt-4 border-t border-brand-500/10 group-hover:border-brand-500/30 transition-colors">
                                <a href="<?= site_url('book?service_id=' . (int)$svc['id']) ?>" class="inline-flex w-full items-center justify-center gap-2 bg-brand-500 px-6 py-3.5 text-sm font-bold font-display uppercase tracking-widest text-zinc-950 hover:bg-white hover:text-zinc-950 transition-colors shadow-lg shadow-brand-500/20">
                                    <?= lang('Site.services_page.book') ?> <i data-lucide="arrow-right" class="size-4"></i>
                                </a>
                            </div>
                            
                            <!-- Subtle hover background effect -->
                            <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
