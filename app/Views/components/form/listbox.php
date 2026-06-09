<?php
/**
 * Component: Alpine.js Listbox (rich dropdown — replaces native <select>)
 *
 * @param string $name        Input name (required)
 * @param array  $options     [['value'=>..,'label'=>..,'desc'=>?,'icon'=>?], ...] (required)
 * @param string $label       Label text (optional)
 * @param string $id          DOM id (defaults to $name)
 * @param string $value       Currently selected value (defaults to old($name))
 * @param string $placeholder Placeholder when nothing selected (optional)
 * @param bool   $required    Required flag (default: false)
 * @param string $helpText    Helper text below (optional)
 */

$id          = $id ?? $name;
$value       = $value ?? old($name);
$required    = $required ?? false;
$label       = $label ?? null;
$helpText    = $helpText ?? null;
$placeholder = $placeholder ?? 'Select an option';
$hasError    = session('errors.' . $name) ? true : false;

// Find the currently selected option, fall back to the first one
$selected = null;
foreach ($options as $o) {
    if ((string)($o['value'] ?? '') === (string)$value) { $selected = $o; break; }
}
if (!$selected && !empty($options)) {
    // No matching value — show placeholder rather than picking first by default
    $selected = ['value' => '', 'label' => $placeholder, 'desc' => null, 'icon' => null];
}
?>
<div>
    <?php if (isset($label)): ?>
        <label for="<?= esc($id) ?>" class="block text-sm/6 font-medium text-gray-900 dark:text-white"><?= esc($label) ?></label>
    <?php endif; ?>

    <?php
    $xData = '{
        open: false,
        selected: ' . json_encode($selected) . ',
        options: ' . json_encode($options) . ',
        choose(o) { this.selected = o; this.open = false; this.$nextTick(() => window.lucide && lucide.createIcons()); }
    }';
    ?>
    <div x-data="<?= htmlspecialchars($xData, ENT_QUOTES, 'UTF-8') ?>"
         @keydown.escape.window="open = false"
         @click.outside="open = false"
         class="relative mt-2">

        <input type="hidden" name="<?= esc($name) ?>" id="<?= esc($id) ?>" :value="selected.value" <?= $required ? 'required' : '' ?>>

        <button type="button"
                @click="open = !open"
                :aria-expanded="open"
                aria-haspopup="listbox"
                class="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-brand-500 <?= $hasError ? 'outline-red-300 focus:outline-red-600 dark:outline-red-500/30 dark:focus:outline-red-500' : '' ?>">
            <span class="flex items-center gap-2.5 min-w-0">
                <template x-if="selected.icon">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                        <i :data-lucide="selected.icon" class="h-3.5 w-3.5"></i>
                    </span>
                </template>
                <span class="block min-w-0 flex-1 truncate">
                    <span class="block truncate" :class="selected.value ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'" x-text="selected.label"></span>
                    <template x-if="selected.desc">
                        <span class="block truncate text-xs text-gray-500 dark:text-gray-400" x-text="selected.desc"></span>
                    </template>
                </span>
            </span>
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <svg class="size-4 text-gray-400 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </span>
        </button>

        <ul x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="listbox"
            class="absolute z-20 mt-1.5 max-h-72 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm dark:bg-gray-800 dark:ring-white/10"
            style="display: none;">
            <template x-for="opt in options" :key="opt.value">
                <li @click="choose(opt)"
                    role="option"
                    :aria-selected="opt.value === selected.value"
                    :class="opt.value === selected.value ? 'bg-brand-50 dark:bg-brand-500/10' : ''"
                    class="group flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <template x-if="opt.icon">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 group-hover:bg-brand-50 group-hover:text-brand-600 dark:group-hover:bg-brand-500/10 dark:group-hover:text-brand-400 transition-colors">
                            <i :data-lucide="opt.icon" class="h-4 w-4"></i>
                        </span>
                    </template>
                    <span class="flex-1 min-w-0">
                        <span class="block font-medium text-gray-900 dark:text-white truncate" x-text="opt.label"></span>
                        <template x-if="opt.desc">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate" x-text="opt.desc"></span>
                        </template>
                    </span>
                    <svg x-show="opt.value === selected.value" class="size-4 text-brand-600 dark:text-brand-400 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                </li>
            </template>
        </ul>
    </div>

    <?php if ($hasError): ?>
        <p class="mt-2 text-sm text-red-600 dark:text-red-400" id="<?= esc($id) ?>-error"><?= esc(session('errors.' . $name)) ?></p>
    <?php elseif (isset($helpText)): ?>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" id="<?= esc($id) ?>-description"><?= esc($helpText) ?></p>
    <?php endif; ?>
</div>
<?php
unset($id, $value, $required, $label, $helpText, $placeholder, $hasError, $selected, $options, $name);
?>
