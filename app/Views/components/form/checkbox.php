<?php
/**
 * Component: Form Checkbox
 * 
 * @param string $name        The input name attribute (required)
 * @param string $label       The label text (required)
 * @param string $id          The input id attribute (defaults to $name)
 * @param string $description Help text below the label (optional)
 * @param bool   $checked     Whether the checkbox is checked (default: false)
 * @param bool   $required    Whether the checkbox is required (default: false)
 */

$id = $id ?? $name;
$checked = $checked ?? false;
$required = $required ?? false;
$hasError = session('errors.' . $name) ? true : false;
?>
<div class="flex items-center gap-3">
    <div class="flex h-6 shrink-0 items-center">
        <div class="group grid size-4 grid-cols-1">
            <input 
                type="checkbox" 
                name="<?= esc($name) ?>" 
                id="<?= esc($id) ?>" 
                <?= $checked ? 'checked' : '' ?>
                <?= $required ? 'required' : '' ?>
                <?= isset($description) ? 'aria-describedby="' . esc($id) . '-description"' : '' ?>
                class="col-start-1 row-start-1 appearance-none rounded-sm border <?= $hasError ? 'border-red-300 dark:border-red-500/50' : 'border-gray-300 dark:border-white/10' ?> bg-white checked:border-brand-600 checked:bg-brand-600 indeterminate:border-brand-600 indeterminate:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto dark:bg-white/5 dark:checked:border-brand-500 dark:checked:bg-brand-500"
            >
            <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25 dark:group-has-disabled:stroke-white/25">
                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-checked:opacity-100" />
                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-indeterminate:opacity-100" />
            </svg>
        </div>
    </div>
    <div class="text-sm/6">
        <label for="<?= esc($id) ?>" class="font-medium <?= $hasError ? 'text-red-900 dark:text-red-400' : 'text-gray-900 dark:text-white' ?>"><?= $label // Unescaped to allow HTML like links in terms of service ?></label>
        <?php if (isset($description)): ?>
            <p id="<?= esc($id) ?>-description" class="text-gray-500 dark:text-gray-400"><?= esc($description) ?></p>
        <?php endif; ?>
        <?php if ($hasError): ?>
            <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="<?= esc($id) ?>-error"><?= esc(session('errors.' . $name)) ?></p>
        <?php endif; ?>
    </div>
</div>
<?php
// Prevent variable leaking into next component call
unset($name, $label, $id, $description, $checked, $required, $hasError);
?>
