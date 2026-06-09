<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$style = $s->get('frontend_layout_style', 'wide');     // wide | boxed | centered
$width = $s->get('frontend_container_width', 'max-w-7xl'); // tailwind max-w utility

// Language settings
$defaultLang  = $s->get('salon_default_lang', 'en');
$enabledLangs = $s->get('frontend_enabled_langs', 'en,si,ta');
$enabledLangs = array_filter(array_map('trim', explode(',', (string)$enabledLangs)));
if (empty($enabledLangs)) $enabledLangs = ['en'];

$allLangs = [
    'en' => ['name' => 'English',  'native' => 'English',  'flag' => '🇬🇧'],
    'si' => ['name' => 'Sinhala',  'native' => 'සිංහල',    'flag' => '🇱🇰'],
    'ta' => ['name' => 'Tamil',    'native' => 'தமிழ்',     'flag' => '🇱🇰'],
];
?>
<form method="POST" action="<?= site_url('admin/settings/frontend') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Public site layout</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Controls how the customer-facing site at the root URL is presented. Changes apply immediately on save.</p>
        </div>

        <!-- Layout style — visual radio cards -->
        <fieldset x-data="{ sel: '<?= esc($style) ?>' }">
            <legend class="block text-sm font-medium text-gray-900 dark:text-white">Layout style</legend>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">Pick how the page is framed.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <?php
                $options = [
                    'wide' => [
                        'label' => 'Wide',
                        'desc'  => 'Edge-to-edge with internal content gutters. Best for marketing-style sites.',
                        'svg'   => '<svg viewBox="0 0 80 50" class="w-full"><rect x="2" y="2" width="76" height="6" rx="1" fill="currentColor" opacity=".25"/><rect x="6" y="14" width="68" height="20" rx="1" fill="currentColor" opacity=".5"/><rect x="2" y="40" width="76" height="6" rx="1" fill="currentColor" opacity=".25"/></svg>',
                    ],
                    'boxed' => [
                        'label' => 'Boxed',
                        'desc'  => 'Centered card with visible side gutters. Feels like a printed brochure.',
                        'svg'   => '<svg viewBox="0 0 80 50" class="w-full"><rect x="0" y="0" width="80" height="50" fill="currentColor" opacity=".08"/><rect x="12" y="4" width="56" height="42" rx="2" fill="currentColor" opacity=".5"/><rect x="16" y="8" width="48" height="4" rx="1" fill="white" opacity=".7"/><rect x="20" y="18" width="40" height="14" rx="1" fill="white" opacity=".9"/><rect x="16" y="38" width="48" height="4" rx="1" fill="white" opacity=".7"/></svg>',
                    ],
                    'centered' => [
                        'label' => 'Centered',
                        'desc'  => 'Edge-to-edge but content kept narrow + centered. Elegant, magazine-like.',
                        'svg'   => '<svg viewBox="0 0 80 50" class="w-full"><rect x="2" y="2" width="76" height="6" rx="1" fill="currentColor" opacity=".25"/><rect x="22" y="14" width="36" height="20" rx="1" fill="currentColor" opacity=".5"/><rect x="2" y="40" width="76" height="6" rx="1" fill="currentColor" opacity=".25"/></svg>',
                    ],
                ];
                foreach ($options as $key => $opt):
                    $isActive = $style === $key;
                ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="frontend_layout_style" value="<?= $key ?>" <?= $isActive ? 'checked' : '' ?> class="sr-only" @change="sel='<?= $key ?>'">
                        <div class="rounded-lg ring-1 transition-all p-4"
                             :class="sel==='<?= $key ?>' ? 'ring-brand-500 bg-brand-50 dark:bg-brand-500/10 shadow-sm shadow-brand-500/10' : 'ring-gray-200 dark:ring-white/10 bg-white dark:bg-gray-900 hover:ring-brand-300 dark:hover:ring-brand-500/40'">
                            <div :class="sel==='<?= $key ?>' ? 'text-brand-500 dark:text-brand-400' : 'text-gray-400 dark:text-gray-500'">
                                <?= $opt['svg'] ?>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($opt['label']) ?></span>
                                <span x-show="sel==='<?= $key ?>" class="inline-flex items-center gap-1 rounded-full bg-brand-500 px-2 py-0.5 text-[10px] font-semibold text-white">
                                    <i data-lucide="check" class="size-3"></i> Active
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 leading-snug"><?= esc($opt['desc']) ?></p>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </div>

    <!-- Content width -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Content max-width</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Applies to all main content containers on the public site.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2" x-data="{ selW: '<?= esc($width) ?>' }">
            <?php
            $widths = [
                'max-w-5xl'  => ['1024px', 'Compact'],
                'max-w-6xl'  => ['1152px', 'Medium'],
                'max-w-7xl'  => ['1280px', 'Default'],
                'max-w-[1440px]' => ['1440px', 'Wide'],
                'max-w-full' => ['100%',    'Full'],
            ];
            foreach ($widths as $w => [$px, $label]): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="frontend_container_width" value="<?= esc($w) ?>" <?= $width === $w ? 'checked' : '' ?> class="sr-only" @change="selW='<?= esc($w, 'js') ?>'">
                    <div class="rounded-lg ring-1 transition-all px-3 py-3 text-center"
                         :class="selW==='<?= esc($w, 'js') ?>' ? 'ring-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'ring-gray-200 dark:ring-white/10 bg-white dark:bg-gray-900 hover:ring-brand-300 dark:hover:ring-brand-500/40'">
                        <p class="text-xs font-semibold text-gray-900 dark:text-white"><?= esc($label) ?></p>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5"><?= esc($px) ?></p>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Languages -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="languages" class="size-4 text-brand-500"></i> Languages
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pick which languages appear in the public site's language switcher, and which one loads for new visitors.</p>
        </div>

        <fieldset>
            <legend class="block text-sm font-medium text-gray-900 dark:text-white">Enabled languages</legend>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">Visitors will be able to switch between these from the navbar globe icon. English stays enabled as the safe fallback.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <?php foreach ($allLangs as $code => $info): $isOn = in_array($code, $enabledLangs, true); $forceOn = ($code === 'en'); ?>
                    <label class="cursor-pointer">
                        <input type="checkbox" name="frontend_enabled_langs[]" value="<?= $code ?>" <?= $isOn || $forceOn ? 'checked' : '' ?> <?= $forceOn ? 'disabled' : '' ?>
                               class="sr-only peer">
                        <div class="rounded-lg ring-1 transition-all px-4 py-3 flex items-center gap-3 <?= $isOn
                            ? 'ring-brand-500 bg-brand-50 dark:bg-brand-500/10 shadow-sm shadow-brand-500/10'
                            : 'ring-gray-200 dark:ring-white/10 bg-white dark:bg-gray-900 hover:ring-brand-300 dark:hover:ring-brand-500/40' ?>">
                            <span class="text-2xl leading-none"><?= $info['flag'] ?></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($info['native']) ?></p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400"><?= esc($info['name']) ?> · <?= esc($code) ?><?= $forceOn ? ' · always on' : '' ?></p>
                            </div>
                            <?php if ($isOn || $forceOn): ?>
                                <i data-lucide="check-circle-2" class="size-5 text-brand-500"></i>
                            <?php else: ?>
                                <i data-lucide="circle" class="size-5 text-gray-300 dark:text-gray-600"></i>
                            <?php endif; ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <fieldset class="mt-6">
            <legend class="block text-sm font-medium text-gray-900 dark:text-white">Default language</legend>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">First-time visitors will land in this language until they pick another one.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" x-data="{ selLang: '<?= esc($defaultLang) ?>' }">
                <?php foreach ($allLangs as $code => $info): $isActive = $defaultLang === $code; ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="salon_default_lang" value="<?= $code ?>" <?= $isActive ? 'checked' : '' ?> class="sr-only" @change="selLang='<?= $code ?>'">
                        <div class="rounded-lg ring-1 px-3 py-2.5 text-center transition-all"
                             :class="selLang==='<?= $code ?>' ? 'ring-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'ring-gray-200 dark:ring-white/10 bg-white dark:bg-gray-900 hover:ring-brand-300 dark:hover:ring-brand-500/40'">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($info['native']) ?></p>
                            <p class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400 mt-0.5"><?= esc($code) ?></p>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </div>

    <div class="flex items-center justify-between gap-3">
        <a href="<?= site_url('/') ?>" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400">
            <i data-lucide="external-link" class="size-4"></i> Open public site in new tab
        </a>
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save frontend settings
        </button>
    </div>
</form>
