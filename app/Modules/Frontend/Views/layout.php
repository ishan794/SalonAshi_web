<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, string $subview, array $data, string $page */
$salonName = 'SALON ASHI';
$logo      = $s->get('biz_logo');
$logoUrl   = $logo ? base_url('uploads/' . $logo) : null;
$phone     = $s->get('biz_phone');
$email     = $s->get('biz_email');
$address   = $s->get('biz_address');
$hours     = $s->get('biz_hours');
$fb        = $s->get('biz_facebook');
$ig        = $s->get('biz_instagram');

// ── Layout style + content width (configurable from Settings → Public site) ──
$layoutStyle    = $s->get('frontend_layout_style', 'wide');         // wide | boxed | centered
$containerWidth = $s->get('frontend_container_width', 'max-w-7xl'); // tailwind max-w utility
$allowedW = ['max-w-5xl','max-w-6xl','max-w-7xl','max-w-[1440px]','max-w-full'];
if (! in_array($containerWidth, $allowedW, true)) $containerWidth = 'max-w-7xl';
// Centered style forces a narrower max-width
if ($layoutStyle === 'centered') $containerWidth = 'max-w-5xl';
// Expose to sub-views (they read this constant when building their own max-w wrappers)
if (! defined('FRONTEND_CONTAINER_W')) define('FRONTEND_CONTAINER_W', $containerWidth);

$isBoxed    = $layoutStyle === 'boxed';
$bodyShell  = 'bg-zinc-950';

// When boxed: navbar + content + footer all live INSIDE a centered card with shadow + side gutters
$cardOpen  = $isBoxed
    ? '<div class="mx-auto my-4 sm:my-6 lg:my-8 ' . esc($containerWidth) . ' bg-zinc-900 shadow-2xl shadow-black/50 ring-1 ring-white/10 overflow-hidden relative">'
    : '';
$cardClose = $isBoxed ? '</div>' : '';

// In boxed mode the navbar fills the card; in wide/centered it spans the viewport up to the chosen width.
$navWidthCls    = $isBoxed ? 'max-w-full' : esc($containerWidth);
$footerWidthCls = $isBoxed ? 'max-w-full' : esc($containerWidth);

// ── Language switcher ──
$currentLocale = service('request')->getLocale() ?: 'en';
$enabledLangs  = $s->get('frontend_enabled_langs', 'en,si,ta');
$enabledLangs  = array_filter(array_map('trim', explode(',', (string)$enabledLangs)));
if (empty($enabledLangs)) $enabledLangs = ['en'];
$langLabels = [
    'en' => ['name' => 'English',   'short' => 'EN'],
    'si' => ['name' => 'සිංහල',     'short' => 'SI'],
    'ta' => ['name' => 'தமிழ்',      'short' => 'TA'],
];

// ── SEO meta resolution ──
// Page slug maps to setting keys (seo_<slug>_title / seo_<slug>_description / seo_<slug>_keywords).
$seoPage = $page ?? 'home';
$seoTitle = $s->get('seo_' . $seoPage . '_title')
    ?: ($title ?? $s->get('seo_default_title') ?: $salonName);
$seoDesc = $s->get('seo_' . $seoPage . '_description')
    ?: $s->get('seo_default_description')
    ?: 'Book your next appointment at ' . $salonName . ' online in seconds.';
$seoKeywords = $s->get('seo_' . $seoPage . '_keywords') ?: $s->get('seo_default_keywords');
$seoOgImage  = $s->get('seo_default_og_image') ? base_url('uploads/' . $s->get('seo_default_og_image')) : $logoUrl;
$seoCanonical = current_url();
$seoRobots   = $s->get('seo_default_robots') ?: 'index, follow';
$seoTwitterHandle = trim((string) $s->get('seo_twitter_handle'));
?>
<!DOCTYPE html>
<html lang="<?= esc($currentLocale) ?>" class="h-full dark bg-zinc-950 text-gray-300">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalonCMS</title>
    <link rel="icon" type="image/png" href="<?= base_url('uploads/favicon.png') ?>">

    <!-- SEO meta -->
    <meta name="description" content="<?= esc($seoDesc) ?>">
    <?php if ($seoKeywords): ?><meta name="keywords" content="<?= esc($seoKeywords) ?>"><?php endif; ?>
    <meta name="robots" content="<?= esc($seoRobots) ?>">
    <link rel="canonical" href="<?= esc($seoCanonical) ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="<?= esc($salonName) ?>">
    <meta property="og:title"       content="<?= esc($seoTitle) ?>">
    <meta property="og:description" content="<?= esc($seoDesc) ?>">
    <meta property="og:url"         content="<?= esc($seoCanonical) ?>">
    <meta property="og:locale"      content="<?= esc($currentLocale) ?>">
    <?php if ($seoOgImage): ?><meta property="og:image" content="<?= esc($seoOgImage) ?>"><?php endif; ?>

    <!-- Twitter -->
    <meta name="twitter:card"        content="<?= $seoOgImage ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title"       content="<?= esc($seoTitle) ?>">
    <meta name="twitter:description" content="<?= esc($seoDesc) ?>">
    <?php if ($seoTwitterHandle): ?><meta name="twitter:site" content="@<?= esc(ltrim($seoTwitterHandle, '@')) ?>"><?php endif; ?>
    <?php if ($seoOgImage): ?><meta name="twitter:image" content="<?= esc($seoOgImage) ?>"><?php endif; ?>
    <script>
      // Force dark mode always for Salon Ashi
      document.documentElement.classList.add('dark');
    </script>
    <!-- Typography: Roboto for body, Oswald for headings (sharp masculine barbershop aesthetic) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: { extend: {
          // Frontend primary = COPPER/BROWN
          colors: {
            brand: {
              50: '#f9f4f1', 100: '#f1e5df', 200: '#e1c6b8', 300: '#cf9f88', 400: '#c48b68', 
              500: '#b06d48', 600: '#9d583b', 700: '#834731', 800: '#6a3a29', 900: '#563124'
            }
          },
          fontFamily: {
            // Body sans
            sans: ['Roboto','ui-sans-serif','system-ui','-apple-system','Segoe UI','sans-serif'],
            // Display headings
            display: ['Oswald','ui-sans-serif','system-ui','sans-serif'],
          }
        } }
      }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      [x-cloak]{display:none!important}
      /* Force dark backgrounds on shell elements */
      html.dark,
      html.dark body,
      html.dark main { background-color: #09090b !important; } /* zinc-950 */
      /* Native <select> option popup colours in dark mode (Tailwind can't reach option elements). */
      html.dark select { color: #d4d4d8; }
      html.dark select option,
      html.dark select optgroup { background-color: #18181b !important; color: #d4d4d8 !important; }
    </style>
</head>
<body class="h-full antialiased text-gray-300 bg-zinc-950 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] overflow-x-hidden">

<!-- ── Preloader ── -->
<div id="site-preloader" class="fixed inset-0 z-[100] bg-zinc-950 flex flex-col items-center justify-center transition-opacity duration-700">
    <h2 class="text-2xl md:text-3xl font-bold font-display uppercase tracking-widest text-brand-400 mb-2">Welcome to <?= esc($salonName) ?></h2>
    <p class="text-sm font-display tracking-widest uppercase text-gray-500 animate-pulse">Please wait...</p>
</div>
<script>
    window.addEventListener('load', function() {
        const loader = document.getElementById('site-preloader');
        if(loader) {
            setTimeout(() => {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 700);
            }, 500);
        }
    });
</script>

<?= $cardOpen ?>

<!-- ── Navbar ── -->
<header class="<?= $isBoxed ? 'relative' : 'absolute inset-x-0 top-0' ?> z-40">
    <nav class="mx-auto flex <?= $navWidthCls ?> items-center justify-between p-5 lg:px-8"
         x-data="{ mobile: false }">
        <a href="<?= site_url('/') ?>" class="flex items-center group">
            <img src="<?= base_url('uploads/logo.png?v=3') ?>" alt="Salon Ashi" class="h-14 w-auto transform group-hover:scale-105 transition duration-300">
        </a>

        <div class="hidden lg:flex lg:items-center lg:gap-x-8 font-display">
            <a href="<?= site_url('/') ?>" class="text-sm uppercase tracking-widest font-semibold <?= $page === 'home' ? 'text-brand-400' : 'text-gray-400 hover:text-brand-400 transition-colors' ?>">HOME</a>
            <a href="<?= site_url('services') ?>" class="text-sm uppercase tracking-widest font-semibold <?= $page === 'services' ? 'text-brand-400' : 'text-gray-400 hover:text-brand-400 transition-colors' ?>">SERVICES</a>
            <a href="<?= site_url('about') ?>" class="text-sm uppercase tracking-widest font-semibold <?= $page === 'about' ? 'text-brand-400' : 'text-gray-400 hover:text-brand-400 transition-colors' ?>">ABOUT</a>
            <a href="<?= site_url('book') ?>" class="text-sm uppercase tracking-widest font-semibold <?= $page === 'book' ? 'text-brand-400' : 'text-gray-400 hover:text-brand-400 transition-colors' ?>">BOOK</a>
            <a href="<?= site_url('portal') ?>" class="text-sm uppercase tracking-widest font-semibold <?= $page === 'portal' ? 'text-brand-400' : 'text-gray-400 hover:text-brand-400 transition-colors' ?>">PROFILE</a>
        </div>

        <div class="hidden lg:flex lg:items-center lg:gap-x-3"
             x-data="{ langMenu:false }">
            <!-- Language switcher -->
            <?php if (count($enabledLangs) > 1): ?>
            <div class="relative" @click.outside="langMenu=false">
                <button @click="langMenu=!langMenu" type="button" class="inline-flex items-center gap-1.5 h-9 px-2.5 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white" :aria-label="'<?= esc(lang('Site.nav.language')) ?>'">
                    <i data-lucide="globe" class="size-4"></i>
                    <span class="text-xs font-semibold uppercase"><?= esc($langLabels[$currentLocale]['short'] ?? strtoupper($currentLocale)) ?></span>
                    <i data-lucide="chevron-down" class="size-3.5"></i>
                </button>
                <div x-show="langMenu" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-2 w-44 rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-gray-900/5 dark:ring-white/10 z-50">
                    <div class="px-3 py-1.5 text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide"><?= lang('Site.nav.language') ?></div>
                    <?php foreach ($enabledLangs as $code): if (! isset($langLabels[$code])) continue; ?>
                        <a href="<?= site_url('locale/switch/' . $code) ?>" class="flex items-center justify-between gap-2 px-3 py-1.5 text-sm <?= $code === $currentLocale ? 'text-brand-600 dark:text-brand-400 font-semibold bg-brand-50 dark:bg-brand-500/10' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5' ?>">
                            <span><?= esc($langLabels[$code]['name']) ?></span>
                            <?php if ($code === $currentLocale): ?><i data-lucide="check" class="size-4"></i><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Portal Link Removed Since It's In Main Nav -->

            <a href="<?= site_url('book') ?>" class="inline-flex items-center gap-1.5 bg-brand-500 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors font-display uppercase tracking-widest">
                <?= lang('Site.nav.book_now') ?>
            </a>
        </div>

        <button @click="mobile = !mobile" class="lg:hidden -m-2.5 p-2.5 text-gray-700 dark:text-gray-300" aria-label="Menu">
            <i data-lucide="menu" class="size-6"></i>
        </button>

        <!-- Mobile menu -->
        <div x-show="mobile" x-cloak class="fixed inset-0 z-50 lg:hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-black/85 backdrop-blur-sm" @click="mobile = false"></div>
            
            <div class="fixed inset-y-0 right-0 w-full max-w-xs bg-gray-900 text-white p-6 shadow-2xl ring-1 ring-white/10 overflow-y-auto flex flex-col justify-between"
                 x-show="mobile"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                <div>
                    <div class="flex items-center justify-between border-b border-white/5 pb-4">
                        <span class="text-lg font-bold tracking-tight text-white"><?= esc($salonName) ?></span>
                        <button @click="mobile = false" class="rounded-lg p-1.5 hover:bg-white/5 text-gray-400 hover:text-white transition-colors" aria-label="Close menu">
                            <i data-lucide="x" class="size-6"></i>
                        </button>
                    </div>
                    <div class="mt-6 space-y-1 font-display tracking-widest uppercase">
                        <a href="<?= site_url('/') ?>" @click="mobile = false" class="block px-4 py-3 text-base font-semibold text-gray-200 hover:bg-white/5 transition border-l-2 <?= $page === 'home' ? 'border-brand-500 text-brand-400' : 'border-transparent' ?>">HOME</a>
                        <a href="<?= site_url('services') ?>" @click="mobile = false" class="block px-4 py-3 text-base font-semibold text-gray-200 hover:bg-white/5 transition border-l-2 <?= $page === 'services' ? 'border-brand-500 text-brand-400' : 'border-transparent' ?>">SERVICES</a>
                        <a href="<?= site_url('about') ?>" @click="mobile = false" class="block px-4 py-3 text-base font-semibold text-gray-200 hover:bg-white/5 transition border-l-2 <?= $page === 'about' ? 'border-brand-500 text-brand-400' : 'border-transparent' ?>">ABOUT</a>
                        <a href="<?= site_url('book') ?>" @click="mobile = false" class="block px-4 py-3 text-base font-semibold text-gray-200 hover:bg-white/5 transition border-l-2 <?= $page === 'book' ? 'border-brand-500 text-brand-400' : 'border-transparent' ?>">BOOK</a>
                        <a href="<?= site_url('portal') ?>" @click="mobile = false" class="block px-4 py-3 text-base font-semibold text-gray-200 hover:bg-white/5 transition border-l-2 <?= $page === 'portal' ? 'border-brand-500 text-brand-400' : 'border-transparent' ?>">PROFILE</a>

                        <?php if (count($enabledLangs) > 1): ?>
                        <div class="mt-6 pt-6 border-t border-white/5">
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-4"><?= lang('Site.nav.language') ?></div>
                            <div class="flex flex-wrap gap-2 px-4">
                                <?php foreach ($enabledLangs as $code): if (! isset($langLabels[$code])) continue; ?>
                                    <a href="<?= site_url('locale/switch/' . $code) ?>" class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all <?= $code === $currentLocale ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : 'bg-white/5 text-gray-300 hover:bg-white/10' ?>">
                                        <?= esc($langLabels[$code]['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-8 border-t border-white/5 pt-6">
                    <a href="<?= site_url('book') ?>" @click="mobile = false" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3.5 text-center text-base font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600 transition">
                        <?= lang('Site.nav.book_now') ?> <i data-lucide="arrow-right" class="size-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Page content -->
<main class="bg-transparent">
    <?php if (session('flash_error')): ?>
        <div class="absolute top-24 left-1/2 -translate-x-1/2 z-30 max-w-xl w-full px-4">
            <div class="rounded-md bg-red-50 dark:bg-red-500/10 p-3 ring-1 ring-red-200 dark:ring-red-500/30 shadow-lg">
                <p class="text-sm text-red-800 dark:text-red-300"><?= esc(session('flash_error')) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?= view($subview, ['data' => $data, 's' => $s]) ?>
</main>

<!-- ── Footer ── -->
<footer class="bg-zinc-950 border-t border-brand-500/20 text-gray-400 <?= $isBoxed ? '' : 'mt-16' ?>">
    <div class="mx-auto <?= $footerWidthCls ?> px-6 py-12 lg:px-8 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
        <!-- Brand + tagline -->
        <div class="col-span-2 lg:col-span-2">
            <div class="flex items-center">
                <img src="<?= base_url('uploads/logo.png?v=3') ?>" alt="Salon Ashi" class="h-12 w-auto">
            </div>
            <p class="mt-3 text-sm max-w-xs"><?= lang('Site.footer.tagline') ?></p>

            <!-- Contact mini -->
            <ul class="mt-5 space-y-1.5 text-sm">
                <?php if ($phone): ?><li class="flex items-center gap-2"><i data-lucide="phone" class="size-3.5"></i><a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="hover:text-white"><?= esc($phone) ?></a></li><?php endif; ?>
                <?php if ($email): ?><li class="flex items-center gap-2"><i data-lucide="mail" class="size-3.5"></i><a href="mailto:<?= esc($email) ?>" class="hover:text-white break-all"><?= esc($email) ?></a></li><?php endif; ?>
                <?php if ($address): ?><li class="flex items-start gap-2"><i data-lucide="map-pin" class="size-3.5 mt-0.5 shrink-0"></i><span><?= nl2br(esc($address)) ?></span></li><?php endif; ?>
                <?php if ($hours): ?><li class="flex items-center gap-2"><i data-lucide="clock" class="size-3.5"></i><?= esc($hours) ?></li><?php endif; ?>
            </ul>

            <?php if ($fb || $ig): ?>
                <div class="mt-4 flex gap-3">
                    <?php if ($fb): ?><a href="<?= esc($fb) ?>" target="_blank" class="hover:text-white" aria-label="Facebook"><i data-lucide="facebook" class="size-5"></i></a><?php endif; ?>
                    <?php if ($ig): ?><a href="<?= esc($ig) ?>" target="_blank" class="hover:text-white" aria-label="Instagram"><i data-lucide="instagram" class="size-5"></i></a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick links -->
        <div>
            <h3 class="text-sm font-semibold text-white"><?= lang('Site.footer.quick') ?></h3>
            <ul class="mt-3 space-y-1.5 text-sm">
                <li><a href="<?= site_url('/') ?>" class="hover:text-white"><?= lang('Site.nav.home') ?></a></li>
                <li><a href="<?= site_url('services') ?>" class="hover:text-white"><?= lang('Site.nav.services') ?></a></li>
                <li><a href="<?= site_url('book') ?>" class="hover:text-white"><?= lang('Site.hero.book_btn') ?></a></li>
            </ul>
        </div>

        <!-- Company -->
        <div>
            <h3 class="text-sm font-semibold text-white"><?= lang('Site.footer.company') ?></h3>
            <ul class="mt-3 space-y-1.5 text-sm">
                <li><a href="<?= site_url('about') ?>" class="hover:text-white"><?= lang('Site.footer.about') ?></a></li>
                <li><a href="<?= site_url('team') ?>" class="hover:text-white"><?= lang('Site.footer.team') ?></a></li>
                <li><a href="<?= site_url('contact') ?>" class="hover:text-white"><?= lang('Site.footer.contact_us') ?></a></li>
                <li><a href="<?= site_url('portal') ?>" class="hover:text-white"><?= lang('Site.portal.nav') ?></a></li>
                <li><a href="<?= site_url('login') ?>" class="hover:text-white"><?= lang('Site.footer.staff') ?></a></li>
            </ul>
        </div>

        <!-- Legal -->
        <div>
            <h3 class="text-sm font-semibold text-white"><?= lang('Site.footer.legal') ?></h3>
            <ul class="mt-3 space-y-1.5 text-sm">
                <li><a href="<?= site_url('terms') ?>" class="hover:text-white"><?= lang('Site.footer.terms') ?></a></li>
                <li><a href="<?= site_url('privacy') ?>" class="hover:text-white"><?= lang('Site.footer.privacy') ?></a></li>
                <li><a href="<?= site_url('refund') ?>" class="hover:text-white"><?= lang('Site.footer.refund') ?></a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10 text-center py-4 text-xs text-gray-500">© 2026 SalonAshi. <a href="https://flxwaretech.com" target="_blank" rel="noopener" class="hover:text-white underline decoration-dotted transition-colors">Flxware Technologies Pvt Ltd</a> Built with care in Sri Lanka 🇱🇰</div>
</footer>

<?= $cardClose ?>

<script>
  function renderIcons(){ window.lucide && lucide.createIcons(); }
  document.addEventListener('DOMContentLoaded', renderIcons);
  document.addEventListener('alpine:initialized', () => setTimeout(renderIcons, 50));
  document.addEventListener('click', () => setTimeout(renderIcons, 30));

  // Date picker for the booking page
  function datepicker(cfg) {
    return {
      value:  (cfg && cfg.value) || '',
      min:    (cfg && cfg.min)   || '',
      max:    (cfg && cfg.max)   || '',
      open:   false,
      cursor: ((cfg && cfg.value) || new Date().toISOString().slice(0,10)).slice(0,7),
      toggle() { this.open = !this.open; if (this.open && this.value) this.cursor = this.value.slice(0,7); this.$nextTick(renderIcons); },
      formatted() { if (!this.value) return ''; return new Date(this.value+'T00:00').toLocaleDateString(undefined, { weekday:'short', year:'numeric', month:'short', day:'numeric' }); },
      monthLabel() { return new Date(this.cursor+'-01T00:00').toLocaleDateString(undefined, { year:'numeric', month:'long' }); },
      cells() {
        const [y,m] = this.cursor.split('-').map(Number);
        const dow = new Date(y,m-1,1).getDay();
        const days = new Date(y,m,0).getDate();
        const out = []; for(let i=0;i<dow;i++) out.push(null);
        for(let d=1;d<=days;d++) out.push(`${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
        while(out.length<42) out.push(null); return out;
      },
      isDisabled(d) { if(!d) return true; if(this.min&&d<this.min) return true; if(this.max&&d>this.max) return true; return false; },
      cellClass(d) {
        if(!d) return '';
        const t = new Date().toISOString().slice(0,10);
        if(this.value===d) return 'bg-brand-500 text-white shadow-sm';
        if(this.isDisabled(d)) return 'text-gray-300 dark:text-gray-600 cursor-not-allowed';
        if(d===t) return 'ring-1 ring-brand-300 dark:ring-brand-500/40 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10';
        return 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5';
      },
      pick(d) { if(!this.isDisabled(d)){ this.value=d; this.open=false; } },
      pickToday() { const t=new Date().toISOString().slice(0,10); if(!this.isDisabled(t)) this.pick(t); else this.cursor=t.slice(0,7); },
      goToToday() { this.cursor=new Date().toISOString().slice(0,7); },
      prevMonth() { const [y,m]=this.cursor.split('-').map(Number); const d=new Date(y,m-2,1); this.cursor=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`; this.$nextTick(renderIcons); },
      nextMonth() { const [y,m]=this.cursor.split('-').map(Number); const d=new Date(y,m,1); this.cursor=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`; this.$nextTick(renderIcons); },
    };
  }
</script>

<?php
// ── WhatsApp floating widget ──
$waEnabled = $s->get('whatsapp_enabled') === '1';
$waNumber  = preg_replace('/\D+/', '', (string) $s->get('whatsapp_number'));
if ($waEnabled && $waNumber !== ''):
    $waMessage = trim((string) $s->get('whatsapp_default_message'));
    $waTooltip = trim((string) $s->get('whatsapp_tooltip'));
    $waPos     = $s->get('whatsapp_position') === 'bottom-left' ? 'left-4 sm:left-6' : 'right-4 sm:right-6';
    $waTooltipPos = $s->get('whatsapp_position') === 'bottom-left' ? 'left-0' : 'right-0';
    $waHref    = 'https://wa.me/' . $waNumber . ($waMessage !== '' ? '?text=' . rawurlencode($waMessage) : '');
?>
<div class="fixed bottom-4 sm:bottom-6 <?= $waPos ?> z-50"
     x-data="{ open: false, dismissed: false }"
     x-init="setTimeout(() => { if (!dismissed) open = true; }, 1800)">

    <!-- Welcome tooltip (auto-shows once after page load, dismissable) -->
    <?php if ($waTooltip !== ''): ?>
        <div x-show="open && !dismissed" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="absolute bottom-full mb-3 <?= $waTooltipPos ?> w-64 rounded-xl bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10 shadow-xl p-4">
            <button @click="dismissed = true; open = false" class="absolute top-1.5 right-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-white" aria-label="Dismiss">
                <i data-lucide="x" class="size-3.5"></i>
            </button>
            <div class="flex items-start gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-full bg-green-500 text-white shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                </span>
                <div class="flex-1 min-w-0 pr-3">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($salonName) ?></p>
                    <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400"><?= esc($waTooltip) ?></p>
                </div>
            </div>
            <a href="<?= esc($waHref) ?>" target="_blank" rel="noopener" @click="dismissed = true"
               class="mt-3 flex items-center justify-center gap-1.5 w-full rounded-md bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-2">
                <?= lang('Site.whatsapp.start_chat') ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Floating button -->
    <a href="<?= esc($waHref) ?>" target="_blank" rel="noopener"
       @click="dismissed = true"
       class="group relative flex size-14 items-center justify-center rounded-full bg-green-500 hover:bg-green-600 text-white shadow-2xl shadow-green-500/40 ring-4 ring-white dark:ring-gray-900 transition-transform hover:scale-110"
       aria-label="<?= esc(lang('Site.whatsapp.aria')) ?>">
        <!-- Pulse halo -->
        <span class="absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-60 animate-ping"></span>
        <!-- WhatsApp glyph -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="relative size-7"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
    </a>
</div>
<?php endif; ?>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
      duration: 800,
      easing: 'ease-in-out',
      once: true,
      offset: 50
    });
  });
</script>
</body>
</html>
