<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$pages = [
    'home'     => ['label' => 'Home',          'icon' => 'home',         'url' => '/'],
    'services' => ['label' => 'Services',      'icon' => 'sparkles',     'url' => 'services'],
    'book'     => ['label' => 'Book',          'icon' => 'calendar',     'url' => 'book'],
    'about'    => ['label' => 'About us',      'icon' => 'info',         'url' => 'about'],
    'team'     => ['label' => 'Team',          'icon' => 'users',        'url' => 'team'],
    'contact'  => ['label' => 'Contact',       'icon' => 'message-circle','url' => 'contact'],
    'terms'    => ['label' => 'Terms',         'icon' => 'gavel',        'url' => 'terms'],
    'privacy'  => ['label' => 'Privacy',       'icon' => 'shield-check', 'url' => 'privacy'],
    'refund'   => ['label' => 'Refund',        'icon' => 'rotate-ccw',   'url' => 'refund'],
];
?>
<form method="POST" action="<?= site_url('admin/settings/seo') ?>" enctype="multipart/form-data" class="space-y-5"
      x-data="{ tab: 'defaults' }">
    <?= csrf_field() ?>

    <!-- Defaults card -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="search" class="size-4 text-brand-500"></i> SEO &amp; metadata
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Title, meta description and Open Graph image used for search engines and social media previews. Per-page overrides take precedence over defaults.</p>
        </div>

        <!-- Tabs: defaults + per page -->
        <div class="flex flex-wrap gap-2 mb-5">
            <button type="button" @click="tab = 'defaults'"
                    :class="tab === 'defaults' ? 'bg-brand-50 dark:bg-brand-500/15 ring-brand-500 text-brand-700 dark:text-brand-300' : 'bg-white dark:bg-gray-900 ring-gray-200 dark:ring-white/10 text-gray-700 dark:text-gray-300 hover:ring-brand-300'"
                    class="rounded-lg ring-1 px-3 py-2 text-sm font-semibold flex items-center gap-2">
                <i data-lucide="settings" class="size-4"></i> Site defaults
            </button>
            <?php foreach ($pages as $key => $p): ?>
                <button type="button" @click="tab = '<?= $key ?>'"
                        :class="tab === '<?= $key ?>' ? 'bg-brand-50 dark:bg-brand-500/15 ring-brand-500 text-brand-700 dark:text-brand-300' : 'bg-white dark:bg-gray-900 ring-gray-200 dark:ring-white/10 text-gray-700 dark:text-gray-300 hover:ring-brand-300'"
                        class="rounded-lg ring-1 px-3 py-2 text-sm font-semibold flex items-center gap-2">
                    <i data-lucide="<?= esc($p['icon']) ?>" class="size-4"></i> <?= esc($p['label']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Site defaults panel -->
        <div x-show="tab === 'defaults'" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Default title</label>
                <input name="seo_default_title" value="<?= esc($s->get('seo_default_title')) ?>" placeholder="<?= esc($s->get('salon_name', 'SalonCMS')) ?> — Book online" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used when a page has no specific title.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Default description</label>
                <textarea name="seo_default_description" rows="3" placeholder="Book your next appointment at our salon online in seconds…" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500"><?= esc($s->get('seo_default_description')) ?></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Keep under 160 characters for best display in search results.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Default keywords</label>
                <input name="seo_default_keywords" value="<?= esc($s->get('seo_default_keywords')) ?>" placeholder="salon, hair, makeup, bridal, colombo" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Comma-separated. Less impactful these days but harmless.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Default robots</label>
                    <select name="seo_default_robots" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                        <?php $r = $s->get('seo_default_robots') ?: 'index, follow'; ?>
                        <option value="index, follow"     <?= $r === 'index, follow'     ? 'selected' : '' ?>>index, follow (live site)</option>
                        <option value="noindex, nofollow" <?= $r === 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow (hide from search)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Twitter handle</label>
                    <input name="seo_twitter_handle" value="<?= esc($s->get('seo_twitter_handle')) ?>" placeholder="@yoursalon" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Default OG image (1200×630 recommended)</label>
                <?php $og = $s->get('seo_default_og_image'); ?>
                <?php if ($og): ?>
                    <div class="mt-2 flex items-center gap-3">
                        <img src="<?= base_url('uploads/' . $og) ?>" alt="" class="h-16 w-auto rounded ring-1 ring-gray-200 dark:ring-white/10">
                        <label class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1"><input type="checkbox" name="seo_og_image_remove" value="1"> Remove current</label>
                    </div>
                <?php endif; ?>
                <input type="file" name="seo_og_image" accept="image/*" class="mt-2 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-brand-50 dark:file:bg-brand-500/15 file:text-brand-700 dark:file:text-brand-300 file:font-semibold hover:file:bg-brand-100">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used for Facebook / LinkedIn / Twitter previews. Falls back to your logo if not set.</p>
            </div>
        </div>

        <!-- Per-page panels -->
        <?php foreach ($pages as $key => $p): ?>
            <div x-show="tab === '<?= $key ?>'" x-cloak class="space-y-4">
                <div class="rounded-md bg-blue-50 dark:bg-blue-500/10 ring-1 ring-blue-200 dark:ring-blue-500/30 px-3 py-2 text-xs text-blue-800 dark:text-blue-300">
                    Editing SEO for <strong><?= esc($p['label']) ?></strong> page. Leave fields blank to use the site defaults.
                    <a href="<?= site_url($p['url']) ?>" target="_blank" class="ml-1 underline">Preview page →</a>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Page title</label>
                    <input name="seo_<?= $key ?>_title" value="<?= esc($s->get('seo_' . $key . '_title')) ?>" placeholder="<?= esc($p['label']) ?> — <?= esc($s->get('salon_name', 'SalonCMS')) ?>" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Page description</label>
                    <textarea name="seo_<?= $key ?>_description" rows="3" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500"><?= esc($s->get('seo_' . $key . '_description')) ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Page keywords</label>
                    <input name="seo_<?= $key ?>_keywords" value="<?= esc($s->get('seo_' . $key . '_keywords')) ?>" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="flex items-center justify-end">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save SEO settings
        </button>
    </div>
</form>
