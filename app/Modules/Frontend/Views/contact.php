<?php
/** @var \App\Modules\Settings\Models\SettingModel $s */
$phone   = $s->get('biz_phone');
$email   = $s->get('biz_email');
$address = $s->get('biz_address');
$hours   = $s->get('biz_hours');
$fb      = $s->get('biz_facebook');
$ig      = $s->get('biz_instagram');

// ── Map ──
$mapEnabled = $s->get('biz_map_enabled', '1') === '1';
$mapEmbed   = trim((string) $s->get('biz_map_embed', ''));
$mapIframe  = '';
if ($mapEnabled) {
    if ($mapEmbed !== '') {
        // Owner-pasted Google Maps embed. We extract just the iframe and force
        // sensible attributes so it always renders responsively + safely.
        if (preg_match('/<iframe[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $mapEmbed, $m)) {
            $src = $m[1];
            // Only allow google.com/maps/embed URLs
            if (preg_match('#^https://(www\.)?google\.[a-z.]+/maps#i', $src)) {
                $mapIframe = '<iframe src="' . esc($src, 'attr') . '" class="w-full h-full border-0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
            }
        }
    }
    // Fallback: build a basic embed from the address (no API key needed)
    if ($mapIframe === '' && $address !== '') {
        $src = 'https://maps.google.com/maps?q=' . urlencode(trim(preg_replace('/\s+/', ' ', $address))) . '&z=15&output=embed';
        $mapIframe = '<iframe src="' . esc($src, 'attr') . '" class="w-full h-full border-0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
    }
}
?>
<section class="pt-32 pb-12 bg-transparent">
    <div class="mx-auto max-w-3xl px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 bg-brand-500/10 px-4 py-1.5 text-xs font-bold tracking-widest text-brand-400 border border-brand-500/20 font-display uppercase">
            <i data-lucide="message-circle" class="size-4"></i> <?= lang('Site.contact_page.heading') ?>
        </span>
        <h1 class="mt-6 text-4xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase"><?= lang('Site.contact_page.heading') ?></h1>
        <div class="w-16 h-1 bg-brand-500 mx-auto mt-6 mb-4"></div>
        <p class="mt-3 text-lg text-gray-400 font-light"><?= lang('Site.contact_page.lead') ?></p>
    </div>
</section>

<section class="py-12 lg:py-16 bg-transparent">
    <div class="mx-auto max-w-5xl px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-5 gap-8">

        <!-- Info column -->
        <aside class="lg:col-span-2 space-y-6">
            <?php if (session('flash_success')): ?>
                <div class="bg-brand-500/10 border border-brand-500 p-4 text-sm text-brand-400 flex items-start gap-3 shadow-xl font-display uppercase tracking-widest">
                    <i data-lucide="check-circle-2" class="size-5 mt-0.5"></i><span><?= esc(session('flash_success')) ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-zinc-900/80 p-8 border border-brand-500/20 shadow-2xl space-y-6">
                <?php if ($phone): ?>
                    <div class="flex items-start gap-4">
                        <span class="flex size-10 items-center justify-center bg-brand-500 text-zinc-950 shrink-0"><i data-lucide="phone" class="size-5"></i></span>
                        <div>
                            <p class="text-xs font-bold font-display text-brand-400 uppercase tracking-widest"><?= lang('Site.contact_page.phone') ?></p>
                            <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="text-sm font-bold font-sans text-white hover:text-brand-500 transition-colors tracking-widest"><?= esc($phone) ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($email): ?>
                    <div class="flex items-start gap-4">
                        <span class="flex size-10 items-center justify-center bg-brand-500 text-zinc-950 shrink-0"><i data-lucide="mail" class="size-5"></i></span>
                        <div>
                            <p class="text-xs font-bold font-display text-brand-400 uppercase tracking-widest">Email</p>
                            <a href="mailto:<?= esc($email) ?>" class="text-sm font-bold font-sans text-white hover:text-brand-500 transition-colors break-all tracking-widest"><?= esc($email) ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($address): ?>
                    <div class="flex items-start gap-4">
                        <span class="flex size-10 items-center justify-center bg-brand-500 text-zinc-950 shrink-0"><i data-lucide="map-pin" class="size-5"></i></span>
                        <div>
                            <p class="text-xs font-bold font-display text-brand-400 uppercase tracking-widest"><?= lang('Site.contact_page.address') ?></p>
                            <p class="text-sm font-light text-gray-300 tracking-wide mt-1"><?= nl2br(esc($address)) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($hours): ?>
                    <div class="flex items-start gap-4">
                        <span class="flex size-10 items-center justify-center bg-brand-500 text-zinc-950 shrink-0"><i data-lucide="clock" class="size-5"></i></span>
                        <div>
                            <p class="text-xs font-bold font-display text-brand-400 uppercase tracking-widest"><?= lang('Site.contact_page.hours') ?></p>
                            <p class="text-sm font-light text-gray-300 tracking-wide mt-1 whitespace-pre-line"><?= esc($hours) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($fb || $ig): ?>
                    <div class="flex items-center gap-4 pt-6 border-t border-brand-500/20">
                        <?php if ($fb): ?><a href="<?= esc($fb) ?>" target="_blank" class="flex size-10 items-center justify-center bg-zinc-950 border border-white/10 text-white hover:bg-brand-500 hover:text-zinc-950 hover:border-brand-500 transition-colors"><i data-lucide="facebook" class="size-5"></i></a><?php endif; ?>
                        <?php if ($ig): ?><a href="<?= esc($ig) ?>" target="_blank" class="flex size-10 items-center justify-center bg-zinc-950 border border-white/10 text-white hover:bg-brand-500 hover:text-zinc-950 hover:border-brand-500 transition-colors"><i data-lucide="instagram" class="size-5"></i></a><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Contact form -->
        <div class="lg:col-span-3">
            <form method="POST" action="<?= site_url('contact') ?>" class="bg-zinc-900/80 p-8 border border-brand-500/20 shadow-2xl space-y-6">
                <?= csrf_field() ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.contact_page.name') ?> <span class="text-brand-500">*</span></label>
                        <input name="name" required class="w-full bg-zinc-950 border border-white/10 text-white py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans">
                    </div>
                    <div>
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.contact_page.mobile') ?></label>
                        <input name="mobile" placeholder="<?= esc(lang('Site.book.mobile_hint')) ?>" class="w-full bg-zinc-950 border border-white/10 text-white py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans placeholder-gray-600">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.contact_page.email') ?></label>
                    <input name="email" type="email" class="w-full bg-zinc-950 border border-white/10 text-white py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans">
                </div>
                <div>
                    <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.contact_page.message') ?> <span class="text-brand-500">*</span></label>
                    <textarea name="message" rows="6" required class="w-full bg-zinc-950 border border-white/10 text-white py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans"></textarea>
                </div>
                <div class="flex justify-end pt-4 border-t border-brand-500/10">
                    <button type="submit" class="inline-flex items-center gap-3 bg-brand-500 px-8 py-3 text-sm font-bold font-display uppercase tracking-widest text-zinc-950 hover:bg-brand-600 transition-colors shadow-lg shadow-brand-500/20">
                        <i data-lucide="send" class="size-4"></i> <?= lang('Site.contact_page.send') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php if ($mapIframe !== ''): ?>
<!-- ── Location map ── -->
<section class="pb-16 bg-transparent border-t border-brand-500/20 pt-16 mt-8">
    <div class="mx-auto max-w-5xl px-6 lg:px-8">
        <div class="border border-brand-500/20 shadow-2xl bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between gap-4 bg-zinc-950 px-6 py-4 border-b border-brand-500/20">
                <div class="flex items-center gap-3 text-sm font-bold font-display tracking-widest uppercase text-brand-400">
                    <i data-lucide="map-pin" class="size-5"></i> <?= lang('Site.contact_page.find_us') ?>
                </div>
                <?php if ($address): ?>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= esc(urlencode($address)) ?>" target="_blank"
                       class="inline-flex items-center gap-2 text-xs font-bold font-display uppercase tracking-widest text-white hover:text-brand-500 transition-colors">
                        <i data-lucide="navigation" class="size-4"></i> <?= lang('Site.contact_page.directions') ?>
                    </a>
                <?php endif; ?>
            </div>
            <!-- Aspect ratio container -->
            <div class="relative w-full h-72 sm:h-96 lg:h-[32rem] bg-zinc-900 grayscale contrast-125 opacity-80 hover:grayscale-0 hover:opacity-100 transition-all duration-700">
                <?= $mapIframe ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
