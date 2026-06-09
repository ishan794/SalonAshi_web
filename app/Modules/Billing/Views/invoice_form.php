<?php
$customerOptions = array_map(fn($c) => [
    'value' => (int)$c['id'],
    'label' => $c['full_name'],
    'desc'  => phone_local($c['mobile']) . ($c['email'] ? ' · ' . $c['email'] : ''),
], $customers);
?>
<form method="POST" action="<?= site_url('admin/billing/invoices') ?>" class="space-y-6"
      x-data='invoiceForm()'>
    <?= csrf_field() ?>

    <!-- Page heading -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">New Invoice</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pick a customer, add line items, save.</p>
        </div>
        <a href="<?= site_url('admin/billing/invoices') ?>" class="hidden sm:inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
            <i data-lucide="arrow-left" class="size-4"></i> Back to invoices
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- ── Left column (form) ── -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Customer + meta -->
            <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
                <div class="flex items-center gap-2 mb-4">
                    <span class="flex size-8 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                        <i data-lucide="user-round" class="size-4"></i>
                    </span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Bill To</h3>
                </div>

                <?= view('components/form/combobox', [
                    'name'        => 'customer_id',
                    'label'       => 'Customer',
                    'required'    => true,
                    'options'     => $customerOptions,
                    'placeholder' => 'Search by name, mobile or email…',
                    'emptyText'   => 'No customers match',
                    'icon'        => 'user',
                ]) ?>

                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Or <a href="<?= site_url('admin/customers/create') ?>" target="_blank" class="font-medium text-brand-600 dark:text-brand-400 hover:underline">create a new customer →</a>
                </div>

                <!-- Stylist / staff attribution — required so revenue & commission payouts are tracked -->
                <div class="mt-4">
                    <label for="staff_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stylist <span class="text-gray-400 dark:text-gray-500 font-normal">(for revenue &amp; commission)</span>
                    </label>
                    <select id="staff_id" name="staff_id"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">— Unassigned —</option>
                        <?php foreach (($staff ?? []) as $st): ?>
                            <option value="<?= (int) $st['id'] ?>" <?= old('staff_id') == $st['id'] ? 'selected' : '' ?>>
                                <?= esc($st['full_name']) ?><?= !empty($st['role']) ? ' · ' . esc($st['role']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Pick the staff member who performed the service so it shows up in their revenue report and payout statement.
                    </p>
                </div>
            </div>

            <!-- Line items -->
            <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="flex size-8 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                            <i data-lucide="list" class="size-4"></i>
                        </span>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Line items</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <select x-ref="picker" class="rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-xs">
                            <option value="">+ Add a service…</option>
                            <?php foreach ($services as $s): ?>
                                <option value='<?= esc(json_encode(["name"=>$s["name"],"unit_price"=>(float)$s["price"],"tax_pct"=>(float)$s["tax_pct"]])) ?>'>
                                    <?= esc($s['name']) ?> · LKR <?= number_format((float)$s['price'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" @click="addFromPicker($refs.picker)" class="inline-flex items-center gap-1 rounded-md bg-brand-50 px-2.5 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100 dark:bg-brand-500/15 dark:text-brand-300 dark:hover:bg-brand-500/25">
                            <i data-lucide="plus" class="size-3.5"></i> Add
                        </button>
                        <button type="button" @click="addBlank()" class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">
                            <i data-lucide="file-plus" class="size-3.5"></i> Blank
                        </button>
                    </div>
                </div>

                <!-- Empty state -->
                <div x-show="!items.length" class="py-10 text-center">
                    <div class="mx-auto size-12 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                        <i data-lucide="package-open" class="size-6 text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">No items yet</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Use "Add" above to insert a service from the menu, or click "Blank" for a custom line.</p>
                </div>

                <!-- Header row -->
                <div x-show="items.length" class="hidden sm:grid grid-cols-12 gap-2 px-2 pb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10">
                    <div class="col-span-5">Item</div>
                    <div class="col-span-1 text-center">Qty</div>
                    <div class="col-span-2 text-right">Unit</div>
                    <div class="col-span-2 text-right">Tax %</div>
                    <div class="col-span-1 text-right">Total</div>
                    <div class="col-span-1"></div>
                </div>

                <!-- Item rows -->
                <div class="space-y-2 pt-2">
                    <template x-for="(it, i) in items" :key="i">
                        <div class="grid grid-cols-12 gap-2 items-center rounded-lg sm:rounded-none px-2 py-2 sm:py-1.5 ring-1 ring-gray-100 sm:ring-0 dark:ring-white/5">
                            <input type="hidden" :name="`items[${i}][item_type]`" value="service">
                            <input :name="`items[${i}][name]`" x-model="it.name" placeholder="Item name"
                                   class="col-span-12 sm:col-span-5 rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <input type="number" step="0.01" min="0" :name="`items[${i}][qty]`" x-model.number="it.qty" placeholder="1"
                                   class="col-span-4 sm:col-span-1 rounded-md border-gray-300 text-sm text-center focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <input type="number" step="0.01" min="0" :name="`items[${i}][unit_price]`" x-model.number="it.unit_price" placeholder="0.00"
                                   class="col-span-4 sm:col-span-2 rounded-md border-gray-300 text-sm text-right focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <input type="number" step="0.01" min="0" :name="`items[${i}][tax_pct]`" x-model.number="it.tax_pct" placeholder="0"
                                   class="col-span-4 sm:col-span-2 rounded-md border-gray-300 text-sm text-right focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <div class="col-span-11 sm:col-span-1 text-right text-sm font-medium text-gray-900 dark:text-white"
                                 x-text="((Number(it.qty)||0) * (Number(it.unit_price)||0)).toFixed(2)"></div>
                            <button type="button" @click="items.splice(i,1)" class="col-span-1 inline-flex justify-end text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                <i data-lucide="x" class="size-4"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Notes -->
            <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
                <div class="flex items-center gap-2 mb-3">
                    <span class="flex size-8 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                        <i data-lucide="sticky-note" class="size-4"></i>
                    </span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Notes</h3>
                </div>
                <?= view('components/form/textarea',['name'=>'notes','label'=>'','rows'=>2,'placeholder'=>'e.g. Thanks for your visit!']) ?>
            </div>
        </div>

        <!-- ── Right column (totals + actions, sticky) ── -->
        <aside class="lg:col-span-1">
            <div class="sticky top-20 space-y-4">
                <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Summary</h3>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Items</dt>
                            <dd class="font-medium text-gray-900 dark:text-white" x-text="items.length"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Subtotal</dt>
                            <dd class="font-medium text-gray-900 dark:text-white" x-text="'LKR ' + subtotal.toFixed(2)"></dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-500 dark:text-gray-400">Discount</dt>
                            <dd>
                                <input type="number" step="0.01" min="0" name="discount" x-model.number="discount"
                                       class="w-24 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm text-right focus:border-brand-500 focus:ring-brand-500">
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Tax</dt>
                            <dd class="font-medium text-gray-900 dark:text-white" x-text="'LKR ' + tax.toFixed(2)"></dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-white/10 pt-3 mt-3 flex justify-between">
                            <dt class="text-base font-semibold text-gray-900 dark:text-white">Total</dt>
                            <dd class="text-base font-bold text-brand-600 dark:text-brand-400" x-text="'LKR ' + total.toFixed(2)"></dd>
                        </div>
                    </dl>

                    <button type="submit" :disabled="!items.length"
                            class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-md bg-brand-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed disabled:shadow-none dark:disabled:bg-white/10 dark:disabled:text-gray-500">
                        <i data-lucide="check-circle-2" class="size-4"></i>
                        Create invoice
                    </button>
                    <a href="<?= site_url('admin/billing/invoices') ?>" class="mt-2 block text-center text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">Cancel</a>
                </div>

                <div class="rounded-xl bg-gradient-to-br from-brand-50 to-pink-50 dark:from-brand-500/10 dark:to-purple-500/10 p-4 ring-1 ring-brand-200 dark:ring-brand-500/20">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="info" class="size-4 mt-0.5 text-brand-600 dark:text-brand-400 shrink-0"></i>
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            After saving, you can <strong>email</strong> the invoice, share via <strong>WhatsApp</strong>, <strong>print</strong> or <strong>download as PDF</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</form>

<script>
function invoiceForm() {
    return {
        items: [],
        discount: 0,
        get subtotal() { return this.items.reduce((s, i) => s + (Number(i.qty)||0) * (Number(i.unit_price)||0), 0); },
        get tax()      { return this.items.reduce((s, i) => s + (Number(i.qty)||0) * (Number(i.unit_price)||0) * ((Number(i.tax_pct)||0) / 100), 0); },
        get total()    { return Math.max(0, this.subtotal - (Number(this.discount)||0) + this.tax); },

        addBlank() {
            this.items.push({ name: '', qty: 1, unit_price: 0, tax_pct: 0 });
        },
        addFromPicker(sel) {
            if (!sel.value) return;
            try {
                const d = JSON.parse(sel.value);
                this.items.push({ name: d.name, qty: 1, unit_price: d.unit_price, tax_pct: d.tax_pct });
                sel.value = '';
            } catch (e) {}
        },
    };
}
</script>
