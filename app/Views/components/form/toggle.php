<?php
/**
 * Component: Form Toggle
 * 
 * @param string $name       The input name attribute (required)
 * @param string $label      The label text (optional)
 * @param string $description Help text (optional)
 * @param string $id         The input id attribute (defaults to $name)
 * @param bool   $checked    Whether the toggle is on (default: false)
 */

$id = $id ?? $name;
$checked = $checked ?? false;
$label = $label ?? null;
$description = $description ?? null;
?>
<div class="flex items-center justify-between" x-data="{ on: <?= $checked ? 'true' : 'false' ?> }">
    <?php if (isset($label)): ?>
        <span class="flex flex-col">
            <span class="text-sm/6 font-medium text-gray-900 dark:text-white" id="<?= esc($id) ?>-label"><?= esc($label) ?></span>
            <?php if (isset($description)): ?>
                <span class="text-sm text-gray-500 dark:text-gray-400" id="<?= esc($id) ?>-description"><?= esc($description) ?></span>
            <?php endif; ?>
        </span>
    <?php endif; ?>
    
    <button type="button" 
            @click="on = !on" 
            :class="on ? 'bg-brand-600 dark:bg-brand-500' : 'bg-gray-200 dark:bg-white/5'"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" 
            role="switch" 
            :aria-checked="on.toString()"
            aria-labelledby="<?= esc($id) ?>-label"
            <?= isset($description) ? 'aria-describedby="' . esc($id) . '-description"' : '' ?>
    >
        <span aria-hidden="true" 
              :class="on ? 'translate-x-5' : 'translate-x-0'"
              class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-sm ring-1 ring-gray-900/5 transition duration-200 ease-in-out"></span>
    </button>
    <input type="hidden" name="<?= esc($name) ?>" :value="on ? '1' : '0'">
</div>
