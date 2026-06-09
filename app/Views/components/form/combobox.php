<?php
/**
 * Searchable combobox (type-to-filter + keyboard nav).
 *
 * @param string $name        Input name (required)
 * @param array  $options     [['value'=>..,'label'=>..,'desc'=>?], ...] (required)
 * @param string $label       Label text (optional)
 * @param string $id          DOM id (defaults to $name)
 * @param mixed  $value       Selected value (defaults to old($name))
 * @param string $placeholder Empty-state placeholder
 * @param bool   $required    Required flag
 * @param string $helpText    Helper text below
 * @param string $emptyText   Shown when nothing matches the query
 * @param string $icon        Lucide icon for the leading slot (optional)
 */
$id          = $id ?? $name;
$value       = $value ?? old($name);
$required    = $required ?? false;
$label       = $label ?? null;
$helpText    = $helpText ?? null;
$placeholder = $placeholder ?? 'Search…';
$emptyText   = $emptyText ?? 'No matches';
$icon        = $icon ?? null;
$hasError    = session('errors.' . $name) ? true : false;

// Pre-resolve the selected label so the input renders with it on first paint (no flash).
$selectedLabel = '';
foreach ($options as $o) {
    if ((string)($o['value'] ?? '') === (string)$value) {
        $selectedLabel = $o['label'] ?? '';
        break;
    }
}
?>
<div>
    <?php if ($label): ?>
        <label for="<?= esc($id) ?>" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100"><?= esc($label) ?><?= $required ? ' <span class="text-red-500">*</span>' : '' ?></label>
    <?php endif; ?>

    <?php
    $xData = '{
        options: ' . json_encode(array_values($options), JSON_UNESCAPED_UNICODE) . ',
        value: ' . json_encode((string)$value) . ',
        query: ' . json_encode($selectedLabel) . ',
        open: false,
        highlight: 0,
        get filtered() {
            if (!this.query || this.query === this.selectedLabel()) return this.options;
            const q = this.query.toLowerCase().trim();
            const qDigits = q.replace(/\D/g, "");
            const digitsMode = qDigits.length >= 3;
            return this.options.filter(o => {
                const label = (o.label || "").toLowerCase();
                const desc  = (o.desc  || "").toLowerCase();
                if (label.includes(q) || desc.includes(q)) return true;
                if (digitsMode) {
                    const allDigits = (label + " " + desc).replace(/\D/g, "");
                    if (allDigits.includes(qDigits)) return true;
                }
                return false;
            });
        },
        selectedLabel() {
            const m = this.options.find(o => String(o.value) === String(this.value));
            return m ? m.label : "";
        },
        pick(opt) {
            this.value = opt.value;
            this.query = opt.label;
            this.open = false;
            this.highlight = 0;
            this.$nextTick(() => window.lucide && lucide.createIcons());
            this.$dispatch("combobox-change", { name: "' . esc($name) . '", value: this.value });
        },
        clear() {
            this.value = "";
            this.query = "";
            this.open = true;
            this.highlight = 0;
            this.$refs.input.focus();
            this.$dispatch("combobox-change", { name: "' . esc($name) . '", value: "" });
        },
        onKey(e) {
            if (e.key === "ArrowDown") {
                e.preventDefault();
                this.open = true;
                this.highlight = Math.min(this.highlight + 1, this.filtered.length - 1);
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                this.highlight = Math.max(this.highlight - 1, 0);
            } else if (e.key === "Enter") {
                if (this.open && this.filtered[this.highlight]) {
                    e.preventDefault();
                    this.pick(this.filtered[this.highlight]);
                }
            } else if (e.key === "Escape") {
                this.open = false;
            }
        },
        onBlur() {
            setTimeout(() => {
                if (!this.open) this.query = this.selectedLabel();
            }, 150);
        }
    }';
    ?>
    <div x-data="<?= htmlspecialchars($xData, ENT_QUOTES, 'UTF-8') ?>"
         @click.outside="open = false; onBlur()"
         class="relative mt-2">

        <input type="hidden" name="<?= esc($name) ?>" :value="value" <?= $required ? 'required' : '' ?>>

        <div class="relative grid grid-cols-1">
            <input x-ref="input"
                   id="<?= esc($id) ?>"
                   type="text"
                   x-model="query"
                   @focus="open = true; highlight = 0"
                   @input="open = true; highlight = 0"
                   @keydown="onKey"
                   placeholder="<?= esc($placeholder) ?>"
                   autocomplete="off"
                   class="col-start-1 row-start-1 block w-full rounded-md bg-white py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500 <?= $icon ? 'pl-10' : 'pl-3' ?> pr-16 <?= $hasError ? 'outline-red-300 focus:outline-red-600 dark:outline-red-500/30 dark:focus:outline-red-500' : '' ?>">

            <?php if ($icon): ?>
                <i data-lucide="<?= esc($icon) ?>" class="pointer-events-none col-start-1 row-start-1 ml-3 size-4 self-center text-gray-400 dark:text-gray-500"></i>
            <?php endif; ?>

            <div class="col-start-1 row-start-1 mr-2 flex items-center justify-self-end gap-1">
                <button type="button" x-show="value" x-cloak @click="clear()" tabindex="-1"
                        class="inline-flex size-6 items-center justify-center rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" aria-label="Clear">
                    <i data-lucide="x" class="size-3.5"></i>
                </button>
                <button type="button" @click="open = !open; if(open) $refs.input.focus()" tabindex="-1"
                        class="inline-flex size-6 items-center justify-center rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <i data-lucide="chevron-down" class="size-4 transition" :class="open ? 'rotate-180' : ''"></i>
                </button>
            </div>
        </div>

        <ul x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            role="listbox"
            class="absolute z-30 mt-1.5 max-h-64 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-gray-900/5 focus:outline-none sm:text-sm dark:bg-gray-800 dark:ring-white/10">

            <template x-if="!filtered.length">
                <li class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"><?= esc($emptyText) ?></li>
            </template>

            <template x-for="(opt, i) in filtered" :key="opt.value">
                <li @click="pick(opt)"
                    @mouseenter="highlight = i"
                    role="option"
                    :aria-selected="String(opt.value) === String(value)"
                    :class="i === highlight ? 'bg-brand-50 dark:bg-brand-500/15' : ''"
                    class="group flex cursor-pointer items-start justify-between gap-2 px-3 py-2 transition-colors">
                    <div class="min-w-0 flex-1">
                        <span class="block truncate font-medium text-gray-900 dark:text-white" x-text="opt.label"></span>
                        <template x-if="opt.desc">
                            <span class="block truncate text-xs text-gray-500 dark:text-gray-400" x-text="opt.desc"></span>
                        </template>
                    </div>
                    <template x-if="String(opt.value) === String(value)">
                        <svg class="mt-0.5 size-4 shrink-0 text-brand-600 dark:text-brand-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </template>
                </li>
            </template>
        </ul>
    </div>

    <?php if ($hasError): ?>
        <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= esc(session('errors.' . $name)) ?></p>
    <?php elseif ($helpText): ?>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><?= esc($helpText) ?></p>
    <?php endif; ?>
</div>
