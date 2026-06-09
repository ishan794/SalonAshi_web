<?php
/**
 * Component: Radio Cards (visually rich radio button group)
 *
 * @param string $name      Input name (required)
 * @param array  $options   [['value'=>..,'label'=>..,'desc'=>?,'icon'=>?], ...]
 * @param string $value     Current selection (defaults to old($name))
 * @param string $label     Group label (optional)
 * @param string $helpText  Help text (optional)
 * @param int    $cols      Grid columns at sm+ (default: 2)
 */
$id       = $id ?? $name;
$value    = $value ?? old($name);
$label    = $label ?? null;
$helpText = $helpText ?? null;
$cols     = $cols ?? 2;
$hasError = session('errors.' . $name) ? true : false;
$gridCols = "sm:grid-cols-{$cols}";
?>
<div>
    <?php if ($label): ?>
        <label class="block text-sm/6 font-medium text-gray-900 dark:text-white"><?= esc($label) ?></label>
    <?php endif; ?>
    <div class="mt-2 grid grid-cols-1 gap-3 <?= $gridCols ?>">
        <?php foreach ($options as $i => $opt): ?>
            <?php $checked = ((string)$value === (string)$opt['value']); ?>
            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus-within:ring-2 focus-within:ring-brand-600 focus-within:ring-offset-2 transition-all <?= $checked ? 'border-brand-600 ring-1 ring-brand-600 dark:border-brand-500 dark:ring-brand-500' : 'border-gray-300 dark:border-white/10' ?> dark:bg-white/5">
                <input type="radio" name="<?= esc($name) ?>" value="<?= esc($opt['value']) ?>" <?= $checked ? 'checked' : '' ?> class="sr-only">
                <div class="flex flex-1 items-start gap-3">
                    <?php if (!empty($opt['icon'])): ?>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md <?= $checked ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' ?>">
                            <i data-lucide="<?= esc($opt['icon']) ?>" class="h-4 w-4"></i>
                        </span>
                    <?php endif; ?>
                    <div class="flex flex-col flex-1 min-w-0">
                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?= esc($opt['label']) ?></span>
                        <?php if (!empty($opt['desc'])): ?>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><?= esc($opt['desc']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($checked): ?>
                    <svg class="absolute right-3 top-3 size-5 text-brand-600 dark:text-brand-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                    </svg>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>
    </div>
    <?php if ($hasError): ?>
        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= esc(session('errors.' . $name)) ?></p>
    <?php elseif ($helpText): ?>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><?= esc($helpText) ?></p>
    <?php endif; ?>
</div>
<?php unset($id, $value, $label, $helpText, $cols, $hasError, $gridCols, $options, $name); ?>
