<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data);
$salonName = 'SALON ASHI';
$hours     = $s->get('biz_hours');
$address   = $s->get('biz_address');
$phone     = $s->get('biz_phone');
$email     = $s->get('biz_email');
?>
<!-- ── Hero ── -->
<section x-data="{ mouseX: -1000, mouseY: -1000 }" 
         @mousemove="let rect = $el.getBoundingClientRect(); mouseX = $event.clientX - rect.left; mouseY = $event.clientY - rect.top;" 
         @mouseleave="mouseX = -1000; mouseY = -1000"
         class="relative isolate overflow-hidden bg-zinc-950 pt-20 pb-16 lg:pt-32 lg:pb-24 border-b border-brand-500/20">
         
    <!-- Base Outline Text -->
    <div class="absolute inset-0 flex items-center justify-center -z-10 pointer-events-none opacity-20" data-aos="zoom-in" data-aos-duration="1500">
        <h1 class="text-[12rem] lg:text-[20rem] font-black font-display text-transparent" style="-webkit-text-stroke: 2px #c48b68; text-stroke: 2px #c48b68;">
            <?= strtoupper(str_replace(' ', '', $salonName)) ?>
        </h1>
    </div>

    <!-- Spotlight Filled Text -->
    <div class="absolute inset-0 flex items-center justify-center -z-10 pointer-events-none opacity-80" 
         :style="`mask-image: radial-gradient(circle 250px at ${mouseX}px ${mouseY}px, black 0%, transparent 100%); -webkit-mask-image: radial-gradient(circle 250px at ${mouseX}px ${mouseY}px, black 0%, transparent 100%); transition: -webkit-mask-position 0.1s ease-out, mask-position 0.1s ease-out;`">
        <h1 class="text-[12rem] lg:text-[20rem] font-black font-display text-brand-500" style="-webkit-text-stroke: 2px #c48b68; text-stroke: 2px #c48b68;">
            <?= strtoupper(str_replace(' ', '', $salonName)) ?>
        </h1>
    </div>

    <div class="mx-auto <?= defined("FRONTEND_CONTAINER_W") ? FRONTEND_CONTAINER_W : "max-w-7xl" ?> px-6 lg:px-8 relative z-10 text-center">
        <!-- Portrait -->
        <div class="mx-auto w-[500px] h-[500px] relative flex justify-center items-end" data-aos="fade-up" data-aos-delay="200">
            <img src="<?= base_url('uploads/hero_new.png') ?>" alt="<?= esc($salonName) ?>" class="h-full w-auto object-contain drop-shadow-2xl grayscale hover:grayscale-0 transition-all duration-[800ms]">
            <!-- Bottom fade to blend with background -->
            <div class="absolute bottom-0 inset-x-0 h-32 bg-gradient-to-t from-zinc-950 to-transparent pointer-events-none"></div>
        </div>
        
        <!-- Text Overlay -->
        <div class="mt-[-2rem] lg:mt-[-5rem] relative z-20 pointer-events-none" data-aos="fade-up" data-aos-delay="500">
            <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold tracking-widest text-white font-display uppercase leading-tight" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                Your look, your way book in seconds.
            </h2>
        </div>

        <p class="mt-8 text-lg/8 text-gray-300 max-w-3xl mx-auto font-light">
            Walk into SalonAshi feeling like yourself again. Pick your service, choose your stylist, and book your slot online — no calls, no waiting.
        </p>
        <div class="mt-8" data-aos="fade-up" data-aos-delay="700">
            <a href="<?= site_url('book') ?>" class="inline-flex items-center gap-3 bg-brand-500 px-8 py-4 text-sm font-semibold text-zinc-950 hover:bg-white transition-colors font-display uppercase tracking-widest shadow-lg shadow-brand-500/20">
                Book your appointment <i data-lucide="arrow-right" class="size-4"></i>
            </a>
        </div>
    </div>
</section>

<!-- Info Ribbon (We're Open / Location) -->
<section class="bg-zinc-900/50">
    <div class="mx-auto <?= defined("FRONTEND_CONTAINER_W") ? FRONTEND_CONTAINER_W : "max-w-7xl" ?> flex flex-col md:flex-row">
        <div class="flex-1 py-12 px-6 md:border-r border-brand-500/20 text-center hover:bg-zinc-900 transition-colors" data-aos="fade-right">
            <h3 class="text-brand-400 font-display tracking-widest uppercase text-xl mb-6 flex justify-center items-center gap-2">
                <i data-lucide="clock" class="size-5"></i> Open for you
            </h3>
            <p class="text-gray-300 text-sm uppercase tracking-widest leading-loose">
                Mon – Fri: 9:00 AM – 8:00 PM<br>
                Sat – Sun: 10:00 AM – 6:00 PM
            </p>
        </div>
        <div class="flex-1 py-12 px-6 text-center hover:bg-zinc-900 transition-colors border-t md:border-t-0 border-brand-500/20" data-aos="fade-left">
            <h3 class="text-brand-400 font-display tracking-widest uppercase text-xl mb-6 flex justify-center items-center gap-2">
                <i data-lucide="map-pin" class="size-5"></i> Find us
            </h3>
            <p class="text-gray-300 mb-4 text-sm uppercase tracking-widest leading-loose">
                SalonAshi Unisex Salon<br>
                [Street Address], [City]
            </p>
            <a href="https://maps.google.com/?q=[Street+Address]+[City]" target="_blank" rel="noopener" class="text-brand-400 hover:text-white transition-colors text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                Get directions on Google Maps <i data-lucide="arrow-right" class="size-4"></i>
            </a>
        </div>
    </div>
</section>

<!-- ── Video / About Promo ── -->
<section class="py-16 lg:py-24 relative isolate border-t border-brand-500/20 bg-zinc-950">
    <div class="mx-auto <?= defined("FRONTEND_CONTAINER_W") ? FRONTEND_CONTAINER_W : "max-w-7xl" ?> px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            
            <!-- Text Content -->
            <div data-aos="fade-right">
                <h2 class="text-3xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase leading-tight mb-6">
                    Why SalonAshi?
                </h2>
                <p class="text-gray-400 font-light leading-relaxed mb-8">
                    We're a unisex salon that believes good hair starts with a good conversation. Our trained stylists take the time to understand exactly what you want — whether it's a clean trim, a bold colour change, or a full bridal look. Book online in seconds and walk in ready.
                </p>
                <a href="<?= site_url('book') ?>" class="inline-flex items-center gap-2 bg-brand-500 px-8 py-4 text-sm font-semibold text-zinc-950 hover:bg-white transition-colors font-display uppercase tracking-widest shadow-lg shadow-brand-500/20">
                    Book your appointment <i data-lucide="arrow-right" class="size-4"></i>
                </a>
            </div>

            <!-- Video/Image Container -->
            <div class="relative group" data-aos="fade-left">
                <!-- Decorative Frame -->
                <div class="absolute -inset-4 border border-brand-500/30 transform translate-x-3 translate-y-3 group-hover:translate-x-0 group-hover:translate-y-0 transition-transform duration-500 z-0 hidden sm:block"></div>
                
                <div class="relative aspect-[4/3] bg-zinc-900 overflow-hidden shadow-2xl z-10 border border-brand-500/20">
                    <video src="<?= base_url('uploads/video.mp4') ?>" autoplay loop muted playsinline class="size-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-700 group-hover:scale-105"></video>
                    
                    <div class="absolute inset-0 bg-brand-500/10 mix-blend-multiply pointer-events-none"></div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- ── Services preview ── -->
<section class="py-16 lg:py-24 relative isolate border-t border-brand-500/20">
    <div class="mx-auto <?= defined("FRONTEND_CONTAINER_W") ? FRONTEND_CONTAINER_W : "max-w-7xl" ?> px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase">OUR SERVICES</h2>
            <div class="w-16 h-1 bg-brand-500 mx-auto mt-6"></div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-0 border border-brand-500/20" data-aos="fade-up" data-aos-delay="200">
            <?php foreach (array_slice($services, 0, 4) as $svc): ?>
                <a href="<?= site_url('services') ?>" class="group relative aspect-square bg-zinc-900 border-b lg:border-b-0 lg:border-r border-brand-500/20 last:border-0 overflow-hidden flex flex-col items-center justify-center p-6 text-center hover:bg-brand-500 transition-colors duration-500">
                    <div class="mb-4 text-brand-400 group-hover:text-zinc-950 transition-colors">
                        <i data-lucide="scissors" class="size-10 mx-auto"></i>
                    </div>
                    <h3 class="text-lg font-bold font-display uppercase tracking-widest text-white group-hover:text-zinc-950 transition-colors"><?= esc($svc['name']) ?></h3>
                    <p class="mt-2 text-brand-500 group-hover:text-zinc-800 transition-colors font-display text-xl">LKR <?= number_format((float)$svc['price'], 0) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-12 text-center">
            <a href="<?= site_url('services') ?>" class="inline-flex items-center gap-2 bg-brand-500 px-8 py-4 text-sm font-semibold text-zinc-950 hover:bg-white transition-colors font-display uppercase tracking-widest shadow-lg shadow-brand-500/20">
                ALL SERVICES & PRICES <i data-lucide="arrow-right" class="size-4"></i>
            </a>
        </div>
    </div>
</section>

<!-- ── Testimonials ── -->
<section class="py-16 lg:py-24 bg-zinc-900/50 border-t border-brand-500/20">
    <div class="mx-auto <?= defined("FRONTEND_CONTAINER_W") ? FRONTEND_CONTAINER_W : "max-w-7xl" ?> px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase">What our clients say</h2>
            <div class="w-16 h-1 bg-brand-500 mx-auto mt-6"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-aos="fade-up" data-aos-delay="200">
            <!-- Testimonial 1 -->
            <div class="bg-zinc-950 border border-white/10 p-8 relative hover:border-brand-500/30 transition-colors">
                <i data-lucide="quote" class="size-10 text-brand-500/20 absolute top-4 right-4"></i>
                <div class="flex text-brand-500 mb-4">
                    <i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i>
                </div>
                <p class="text-gray-300 font-light leading-relaxed mb-6">"Booked a haircut online in under a minute. Nimal knew exactly what I wanted without me having to explain twice. Best cut I've had in years."</p>
                <p class="text-brand-400 font-display tracking-widest uppercase text-sm">— Kasun R., Colombo</p>
            </div>
            <!-- Testimonial 2 -->
            <div class="bg-zinc-950 border border-white/10 p-8 relative hover:border-brand-500/30 transition-colors">
                <i data-lucide="quote" class="size-10 text-brand-500/20 absolute top-4 right-4"></i>
                <div class="flex text-brand-500 mb-4">
                    <i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i>
                </div>
                <p class="text-gray-300 font-light leading-relaxed mb-6">"Sanduni did my bridal makeup and I felt absolutely beautiful. She was calm, professional, and patient throughout the entire session."</p>
                <p class="text-brand-400 font-display tracking-widest uppercase text-sm">— Dilhani P., Kandy</p>
            </div>
            <!-- Testimonial 3 -->
            <div class="bg-zinc-950 border border-white/10 p-8 relative hover:border-brand-500/30 transition-colors">
                <i data-lucide="quote" class="size-10 text-brand-500/20 absolute top-4 right-4"></i>
                <div class="flex text-brand-500 mb-4">
                    <i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i><i data-lucide="star" class="size-4 fill-current"></i>
                </div>
                <p class="text-gray-300 font-light leading-relaxed mb-6">"Finally a salon that lets you book without calling. Clean space, friendly staff, and my manicure lasted three weeks. Coming back for sure."</p>
                <p class="text-brand-400 font-display tracking-widest uppercase text-sm">— Thilini S., Kandy</p>
            </div>
        </div>
    </div>
</section>

<!-- ── Stylists (Trending Styles equivalent) ── -->
<?php if (!empty($staff)): ?>
<section class="py-16 lg:py-24 bg-zinc-900/30 border-t border-brand-500/20">
    <div class="mx-auto <?= defined("FRONTEND_CONTAINER_W") ? FRONTEND_CONTAINER_W : "max-w-7xl" ?> px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase">TRENDING STYLES</h2>
            <div class="w-16 h-1 bg-brand-500 mx-auto mt-6"></div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-0 border border-brand-500/20 max-w-5xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            <?php foreach (array_slice($staff, 0, 3) as $st): ?>
                <div class="group relative aspect-[3/4] overflow-hidden bg-zinc-800 border-b sm:border-b-0 sm:border-r border-brand-500/20 last:border-0 hover:border-brand-500 transition-colors grayscale hover:grayscale-0">
                    <div class="absolute inset-0 bg-zinc-900 flex items-center justify-center text-zinc-800">
                        <i data-lucide="user" class="size-24"></i>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/40 to-transparent opacity-90 group-hover:opacity-60 transition-opacity"></div>
                    <div class="absolute bottom-0 inset-x-0 p-6 text-center transform translate-y-2 group-hover:translate-y-0 transition-transform">
                        <h3 class="text-lg font-bold font-display uppercase tracking-widest text-white"><?= esc($st['full_name']) ?></h3>
                        <p class="text-brand-400 text-xs font-display uppercase tracking-widest mt-1"><?= esc($st['role'] ?: 'Specialist') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── CTA ── -->
<section class="py-24 relative isolate border-t border-brand-500/20 bg-zinc-900 overflow-hidden">
    <img src="<?= base_url('uploads/cta-bg.jpg') ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-5 grayscale pointer-events-none -z-10 mix-blend-screen">
    
    <div class="mx-auto max-w-4xl px-6 lg:px-8 text-center relative z-10">
        <h2 class="text-4xl md:text-5xl font-bold font-display uppercase tracking-widest text-white mb-4">Ready for your next look?</h2>
        <p class="text-gray-400 font-light text-lg mb-8">Book your appointment online in seconds — no calls, no waiting.</p>
        <a href="<?= site_url('book') ?>" class="inline-flex items-center gap-3 bg-brand-500 px-10 py-5 text-lg font-semibold text-zinc-950 hover:bg-white transition-colors font-display uppercase tracking-widest shadow-2xl shadow-brand-500/20">
            Book your appointment <i data-lucide="arrow-right" class="size-5"></i>
        </a>
    </div>
</section>
