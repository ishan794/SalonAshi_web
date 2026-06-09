<?php
/**
 * Component: Form Actions row (Cancel + Submit)
 *
 * @param string $submitLabel  Submit button text (default: 'Save')
 * @param string $submitIcon   Lucide icon for submit button (default: 'save')
 * @param string $cancelUrl    URL for Cancel link (optional — omit to hide Cancel)
 * @param string $cancelLabel  Cancel link text (default: 'Cancel')
 * @param string $variant      'primary' (brand) | 'danger' (red) (default: primary)
 */
$submitLabel = $submitLabel ?? 'Save';
$submitIcon  = $submitIcon ?? 'save';
$cancelUrl   = $cancelUrl ?? null;
$cancelLabel = $cancelLabel ?? 'Cancel';
$variant     = $variant ?? 'primary';

$btnClass = $variant === 'danger'
    ? 'bg-red-600 hover:bg-red-500 focus-visible:outline-red-600'
    : 'bg-brand-600 hover:bg-brand-500 focus-visible:outline-brand-600';
?>
<div class="flex items-center justify-end gap-x-3 pt-2">
    <?php if ($cancelUrl): ?>
        <a href="<?= esc($cancelUrl) ?>" class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors"><?= esc($cancelLabel) ?></a>
    <?php endif; ?>
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-colors <?= $btnClass ?>">
        <?php if ($submitIcon): ?>
            <i data-lucide="<?= esc($submitIcon) ?>" class="h-4 w-4"></i>
        <?php endif; ?>
        <?= esc($submitLabel) ?>
    </button>
</div>
<?php unset($submitLabel, $submitIcon, $cancelUrl, $cancelLabel, $variant, $btnClass); ?>
