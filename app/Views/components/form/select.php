<?php
/**
 * Component: Form Select
 * 
 * @param string $name       The select name attribute (required)
 * @param array  $options    Array of options (key => value) (required)
 * @param string $label      The label text (optional)
 * @param string $id         The select id attribute (defaults to $name)
 * @param string $selected   The currently selected value (defaults to old($name))
 * @param bool   $required   Whether the select is required (default: false)
 * @param string $helpText   Helper text below the select (optional)
 */

$id = $id ?? $name;
$selected = $selected ?? old($name);
$required = $required ?? false;
$label = $label ?? null;
$helpText = $helpText ?? null;
$hasError = session('errors.' . $name) ? true : false;
?>
<div>
    <?php if (isset($label)): ?>
        <label for="<?= esc($id) ?>" class="block text-sm/6 font-medium text-gray-900 dark:text-white"><?= esc($label) ?></label>
    <?php endif; ?>
    
    <div class="mt-2 grid grid-cols-1">
        <select 
            name="<?= esc($name) ?>" 
            id="<?= esc($id) ?>" 
            <?= $required ? 'required' : '' ?>
            class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-brand-500 <?= $hasError ? 'outline-red-300 focus:outline-red-600 dark:outline-red-500/30 dark:focus:outline-red-500 text-red-900 dark:text-red-400' : '' ?>"
        >
            <?php foreach ($options as $value => $text): ?>
                <option value="<?= esc($value) ?>" <?= $selected == $value ? 'selected' : '' ?>><?= esc($text) ?></option>
            <?php endforeach; ?>
        </select>
        <svg class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
            <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </div>
    
    <?php if ($hasError): ?>
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" id="<?= esc($id) ?>-error"><?= esc(session('errors.' . $name)) ?></p>
    <?php elseif (isset($helpText)): ?>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" id="<?= esc($id) ?>-description"><?= esc($helpText) ?></p>
    <?php endif; ?>
</div>
