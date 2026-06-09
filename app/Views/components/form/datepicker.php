<?php
/**
 * Modern date picker component.
 *
 * @param string $name        Hidden-input name (required)
 * @param string $value       Initial date YYYY-MM-DD (optional)
 * @param string $label       Field label (optional)
 * @param string $id          DOM id (defaults to $name)
 * @param string $min         Earliest selectable date YYYY-MM-DD (optional)
 * @param string $max         Latest selectable date YYYY-MM-DD (optional)
 * @param string $placeholder Placeholder when nothing chosen
 * @param bool   $required    Required flag
 * @param string $onChange    JS expression run when date changes (receives `value`)
 * @param string $helpText    Helper text shown below
 */
$id          = $id ?? $name;
$value       = $value ?? old($name) ?? '';
$label       = $label ?? null;
$placeholder = $placeholder ?? 'Pick a date';
$required    = $required ?? false;
$min         = $min ?? '';
$max         = $max ?? '';
$onChange    = $onChange ?? '';
$helpText    = $helpText ?? null;
?>
<div>
    <?php if ($label): ?>
        <label for="<?= esc($id) ?>" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100"><?= esc($label) ?><?= $required ? ' <span class="text-red-500">*</span>' : '' ?></label>
    <?php endif; ?>

    <div x-data='datepicker({
            value: <?= json_encode((string)$value) ?>,
            min:   <?= json_encode((string)$min) ?>,
            max:   <?= json_encode((string)$max) ?>
         })'
         x-init="$watch(\'value\', v => { <?= $onChange ? '(' . $onChange . ')(v);' : '' ?> })"
         @keydown.escape.window="open = false"
         @click.outside="open = false"
         class="relative mt-2">

        <input type="hidden" name="<?= esc($name) ?>" :value="value" <?= $required ? 'required' : '' ?> id="<?= esc($id) ?>">

        <!-- Trigger -->
        <button type="button" @click="toggle()"
                class="flex w-full items-center gap-2 rounded-md bg-white py-1.5 pl-3 pr-3 text-left text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-brand-500">
            <i data-lucide="calendar" class="pointer-events-none size-4 text-gray-400 dark:text-gray-500"></i>
            <span x-show="value" x-cloak x-text="formatted()" class="flex-1 truncate"></span>
            <span x-show="!value" class="flex-1 truncate text-gray-400 dark:text-gray-500"><?= esc($placeholder) ?></span>
            <i data-lucide="chevron-down" class="size-4 text-gray-400 transition" :class="open ? 'rotate-180' : ''"></i>
        </button>

        <!-- Popover -->
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="absolute z-50 mt-1.5 w-72 origin-top-left rounded-lg bg-white p-3 shadow-lg ring-1 ring-gray-900/10 dark:bg-gray-800 dark:ring-white/10">

            <!-- Header -->
            <div class="flex items-center justify-between px-1 pb-2">
                <button type="button" @click="prevMonth()" class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                    <i data-lucide="chevron-left" class="size-4"></i>
                </button>
                <button type="button" @click="goToToday()" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-300" x-text="monthLabel()"></button>
                <button type="button" @click="nextMonth()" class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                    <i data-lucide="chevron-right" class="size-4"></i>
                </button>
            </div>

            <!-- Day-of-week header -->
            <div class="grid grid-cols-7 text-center text-[10px] font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500 pb-1">
                <div>S</div><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div>
            </div>

            <!-- Day cells -->
            <div class="grid grid-cols-7 gap-1">
                <template x-for="(c, i) in cells()" :key="i">
                    <button type="button"
                            x-show="c"
                            @click="pick(c)"
                            :disabled="isDisabled(c)"
                            :class="cellClass(c)"
                            class="aspect-square rounded-md text-sm font-medium transition-colors"
                            x-text="c ? +c.split('-')[2] : ''"></button>
                </template>
            </div>

            <!-- Footer actions -->
            <div class="mt-2.5 flex items-center justify-between border-t border-gray-100 dark:border-white/10 pt-2.5">
                <button type="button" @click="clearValue()" :disabled="!value"
                        class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white disabled:opacity-40 disabled:cursor-not-allowed">Clear</button>
                <button type="button" @click="pickToday()"
                        class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">Today</button>
            </div>
        </div>
    </div>

    <?php if ($helpText): ?>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><?= esc($helpText) ?></p>
    <?php endif; ?>
</div>

<?php /* datepicker() JS is loaded once in app/Views/layout/admin.php and frontend layout */ ?>
