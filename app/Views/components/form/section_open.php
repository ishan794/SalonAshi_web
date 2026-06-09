<?php
/**
 * Component: Form Section — opening tags (pair with section_close).
 *
 * Usage:
 *   <?= view('components/form/section_open', ['title'=>'Identity', 'description'=>'…', 'icon'=>'tag']) ?>
 *       <?= view('components/form/input', [...]) ?>
 *       <?= view('components/form/listbox', [...]) ?>
 *   <?= view('components/form/section_close') ?>
 *
 * @param string $title       Section heading
 * @param string $description Subtext (optional)
 * @param string $icon        Lucide icon name (optional)
 */
$title       = $title ?? '';
$description = $description ?? null;
$icon        = $icon ?? null;
?>
<div class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-3 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-900/5 dark:ring-white/10 rounded-xl px-6 py-6 sm:px-8">
    <div class="md:col-span-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <?php if ($icon): ?>
                <i data-lucide="<?= esc($icon) ?>" class="h-4 w-4 text-brand-600 dark:text-brand-400"></i>
            <?php endif; ?>
            <?= esc($title) ?>
        </h2>
        <?php if ($description): ?>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?= esc($description) ?></p>
        <?php endif; ?>
    </div>
    <div class="md:col-span-2 space-y-5">
<?php unset($title, $description, $icon); ?>
