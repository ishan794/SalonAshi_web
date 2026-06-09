<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$pages = [
    'about'   => ['label' => 'About us',        'icon' => 'info',          'url' => 'about'],
    'terms'   => ['label' => 'Terms & Conditions','icon' => 'gavel',       'url' => 'terms'],
    'privacy' => ['label' => 'Privacy Policy',  'icon' => 'shield-check',  'url' => 'privacy'],
    'refund'  => ['label' => 'Refund Policy',   'icon' => 'rotate-ccw',    'url' => 'refund'],
];
?>
<form method="POST" action="<?= site_url('admin/settings/pages') ?>" class="space-y-5"
      x-data="{ tab: '<?= esc(array_key_first($pages)) ?>' }">
    <?= csrf_field() ?>

    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Public pages content</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Edit the text shown on About, Terms, Privacy and Refund pages. Basic HTML is allowed (<code>&lt;p&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;a&gt;</code>). Leave blank to keep the sensible default copy.</p>
        </div>

        <!-- Page picker tabs -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-5">
            <?php foreach ($pages as $key => $p): ?>
                <button type="button" @click="tab = '<?= $key ?>'"
                        :class="tab === '<?= $key ?>' ? 'bg-brand-50 dark:bg-brand-500/15 ring-brand-500 text-brand-700 dark:text-brand-300' : 'bg-white dark:bg-gray-900 ring-gray-200 dark:ring-white/10 text-gray-700 dark:text-gray-300 hover:ring-brand-300'"
                        class="rounded-lg ring-1 px-3 py-2.5 text-left transition flex items-center gap-2">
                    <i data-lucide="<?= esc($p['icon']) ?>" class="size-4"></i>
                    <span class="text-sm font-semibold"><?= esc($p['label']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($pages as $key => $p): ?>
            <div x-show="tab === '<?= $key ?>'" x-cloak class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Title</label>
                    <input name="page_<?= $key ?>_title" value="<?= esc($s->get('page_' . $key . '_title')) ?>"
                           placeholder="<?= esc($p['label']) ?>"
                           class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to use the translated default.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Content <span class="text-xs text-gray-500 dark:text-gray-400">(HTML allowed)</span></label>
                    <textarea name="page_<?= $key ?>_content" rows="14"
                              placeholder="Leave blank to use the default placeholder copy…"
                              class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm font-mono focus:border-brand-500 focus:ring-brand-500"><?= esc($s->get('page_' . $key . '_content')) ?></textarea>
                </div>
                <div>
                    <a href="<?= site_url($p['url']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 dark:text-brand-400 hover:text-brand-700">
                        <i data-lucide="external-link" class="size-4"></i> Preview <?= esc($p['label']) ?> page
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="flex items-center justify-end">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save page content
        </button>
    </div>
</form>
