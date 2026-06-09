<?php
/**
 * Component: Form Input
 * 
 * @param string $name       The input name attribute (required)
 * @param string $label      The label text (optional)
 * @param string $type       The input type (default: 'text')
 * @param string $id         The input id attribute (defaults to $name)
 * @param string $value      The input value (defaults to old($name))
 * @param bool   $required   Whether the input is required (default: false)
 * @param string $icon       Lucide icon name for leading icon (optional)
 * @param string $placeholder Placeholder text (optional)
 * @param string $helpText   Helper text below the input (optional)
 * @param string $autocomplete Autocomplete attribute (optional)
 */

$type = $type ?? 'text';
$id = $id ?? $name;
$value = $value ?? old($name);
$required = $required ?? false;
$icon = $icon ?? null;
$label = $label ?? null;
$placeholder = $placeholder ?? null;
$helpText = $helpText ?? null;
$autocomplete = $autocomplete ?? null;
$attrs = $attrs ?? null;
$trailing_html = $trailing_html ?? null;
$trailing_text = $trailing_text ?? null;
if ($trailing_text !== null && $trailing_html === null) {
    $trailing_html = '<span class="pointer-events-none col-start-1 row-start-1 mr-3 self-center justify-self-end text-xs font-medium text-gray-400 dark:text-gray-500">' . esc($trailing_text) . '</span>';
}
$hasError = session('errors.' . $name) ? true : false;
?>
<div>
    <?php if (isset($label)): ?>
        <label for="<?= esc($id) ?>" class="block text-sm/6 font-medium text-gray-900 dark:text-white"><?= esc($label) ?></label>
    <?php endif; ?>
    
    <div class="mt-2 grid grid-cols-1 relative">
        <input 
            <?= isset($attrs) && isset($attrs[':type']) ? ':type="' . esc($attrs[':type']) . '"' : 'type="' . esc($type) . '"' ?>
            name="<?= esc($name) ?>" 
            id="<?= esc($id) ?>" 
            <?= isset($autocomplete) ? 'autocomplete="' . esc($autocomplete) . '"' : '' ?>
            <?= $required ? 'required' : '' ?>
            <?= isset($placeholder) ? 'placeholder="' . esc($placeholder) . '"' : '' ?>
            <?php if(isset($attrs)) { foreach($attrs as $k => $v) { if($k !== ':type') { echo ' ' . esc($k) . '="' . esc($v) . '"'; } } } ?>
            value="<?= esc($value) ?>" 
            class="col-start-1 row-start-1 block w-full rounded-md bg-white py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500 <?= isset($icon) ? 'pl-10' : 'pl-3' ?> isset($trailing_html) || $hasError 'pr-10' 'pr-3' 'outline-red-300 focus:outline-red-600 dark:outline-red-500/30 dark:focus:outline-red-500 text-red-900 dark:text-red-400 placeholder:text-red-300 dark:placeholder:text-red-500/50' ''"
        >
        
        <?php if (isset($icon)): ?>
            <i data-lucide="<?= esc($icon) ?>" class="pointer-events-none col-start-1 row-start-1 ml-3 size-5 self-center <?= $hasError ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-gray-500' ?>"></i>
        <?php endif; ?>

        <?php if (isset($trailing_html)): ?>
            <?= $trailing_html ?>
        <?php elseif ($hasError): ?>
            <div class="pointer-events-none col-start-1 row-start-1 mr-3 self-center justify-self-end">
                <i data-lucide="alert-circle" class="size-5 text-red-500 dark:text-red-400"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($hasError): ?>
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" id="<?= esc($id) ?>-error"><?= esc(session('errors.' . $name)) ?></p>
    <?php elseif (isset($helpText)): ?>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" id="<?= esc($id) ?>-description"><?= esc($helpText) ?></p>
    <?php endif; ?>
</div>
<?php
// Prevent variable leaking into next component call
unset($type, $id, $value, $required, $icon, $label, $placeholder, $helpText, $autocomplete, $attrs, $trailing_html, $trailing_text, $name, $hasError);
?>
