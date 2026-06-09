<?php
/**
 * Component: Form Textarea
 * 
 * @param string $name       The textarea name attribute (required)
 * @param string $label      The label text (optional)
 * @param string $id         The textarea id attribute (defaults to $name)
 * @param string $value      The textarea value (defaults to old($name))
 * @param bool   $required   Whether the textarea is required (default: false)
 * @param string $placeholder Placeholder text (optional)
 * @param string $helpText   Helper text below the textarea (optional)
 * @param int    $rows       Number of visible rows (default: 4)
 */

$id = $id ?? $name;
$value = $value ?? old($name);
$required = $required ?? false;
$rows = $rows ?? 4;
$hasError = session('errors.' . $name) ? true : false;
?>
<div>
    <?php if (isset($label)): ?>
        <label for="<?= esc($id) ?>" class="block text-sm/6 font-medium text-gray-900 dark:text-white"><?= esc($label) ?></label>
    <?php endif; ?>
    
    <div class="mt-2">
        <textarea 
            name="<?= esc($name) ?>" 
            id="<?= esc($id) ?>" 
            rows="<?= esc($rows) ?>"
            <?= $required ? 'required' : '' ?>
            <?= isset($placeholder) ? 'placeholder="' . esc($placeholder) . '"' : '' ?>
            <?php if(isset($attrs)) { foreach($attrs as $k => $v) { echo ' ' . esc($k) . '="' . esc($v) . '"'; } } ?>
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500 <?= $hasError ? 'outline-red-300 focus:outline-red-600 dark:outline-red-500/30 dark:focus:outline-red-500 text-red-900 dark:text-red-400 placeholder:text-red-300 dark:placeholder:text-red-500/50' : '' ?>"
        ><?= esc($value) ?></textarea>
    </div>
    
    <?php if ($hasError): ?>
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" id="<?= esc($id) ?>-error"><?= esc(session('errors.' . $name)) ?></p>
    <?php elseif (isset($helpText)): ?>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" id="<?= esc($id) ?>-description"><?= esc($helpText) ?></p>
    <?php endif; ?>
</div>
