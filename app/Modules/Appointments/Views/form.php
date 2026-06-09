<?php
/** @var array $services, $staff, $serviceTypes, $customers, $assignedServices; @var ?array $row; @var ?int $preselectCustomer */
$isEdit  = !empty($row);
$action  = $isEdit ? site_url('admin/appointments/' . $row['id']) : site_url('admin/appointments');
$todayISO = date('Y-m-d');
$defDateISO = $isEdit ? date('Y-m-d', strtotime($row['start_at'])) : $todayISO;
$defSlot    = $isEdit ? date('H:i',   strtotime($row['start_at'])) : '';
$existingTypeId = $isEdit && ! empty($row['services'][0]['service_type_id']) ? (int) $row['services'][0]['service_type_id'] : null;
$defaultType = null;
foreach ($serviceTypes as $t) { if ((int)$t['is_default']) { $defaultType = (int)$t['id']; break; } }
if ($defaultType === null && $serviceTypes) $defaultType = (int) $serviceTypes[0]['id'];
$selectedTypeId = $existingTypeId ?: $defaultType;

$customerOptions = array_map(fn($c) => [
    'value' => (int)$c['id'],
    'label' => $c['full_name'],
    'desc'  => phone_local($c['mobile']) . ($c['email'] ? ' · ' . $c['email'] : ''),
], $customers);
?>
<form method="POST" action="<?= $action ?>" class="max-w-4xl space-y-5"
      x-data='apptForm({
          services:     <?= json_encode(array_values(array_map(fn($s)=>["id"=>(int)$s["id"],"name"=>$s["name"],"duration"=>(int)$s["duration_min"],"price"=>(float)$s["price"]], $services)), JSON_UNESCAPED_UNICODE) ?>,
          types:        <?= json_encode(array_values(array_map(fn($t)=>["id"=>(int)$t["id"],"name"=>$t["name"],"color"=>$t["color"],"multiplier"=>(float)$t["multiplier"]], $serviceTypes)), JSON_UNESCAPED_UNICODE) ?>,
          preselected:  <?= json_encode(array_map("intval", $assignedServices)) ?>,
          initStaff:    <?= (int) ($isEdit ? $row["staff_id"] : 0) ?>,
          initDate:     "<?= esc($defDateISO) ?>",
          initSlot:     "<?= esc($defSlot) ?>",
          initType:     <?= (int) $selectedTypeId ?>,
          availabilityUrl: "<?= site_url("admin/pos/availability") ?>"
      })'>
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white"><?= $isEdit ? 'Edit appointment ' . esc($row['code']) : 'New appointment' ?></h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Same slot-picker flow as the POS — only available time slots can be booked.</p>
        </div>
        <a href="<?= site_url('admin/appointments') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-white dark:bg-white/5 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50">
            <i data-lucide="x" class="size-4"></i> Cancel
        </a>
    </div>

    <!-- Step 1: Customer -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex size-6 items-center justify-center rounded-full bg-brand-500 text-white text-xs font-bold">1</span>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Customer</h3>
        </div>
        <?= view('components/form/combobox', [
            'name'        => 'customer_id',
            'options'     => $customerOptions,
            'value'       => $isEdit ? (int)$row['customer_id'] : ($preselectCustomer ?: ''),
            'placeholder' => 'Search by name, mobile or email…',
            'emptyText'   => 'No customers match',
            'icon'        => 'user',
        ]) ?>
    </div>

    <!-- Step 2: Services + Service Type -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex size-6 items-center justify-center rounded-full bg-brand-500 text-white text-xs font-bold">2</span>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Services &amp; type</h3>
        </div>

        <!-- Service type pills (single-select) -->
        <?php if (count($serviceTypes) > 1): ?>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Service tier</label>
            <div class="flex flex-wrap gap-2 mb-4">
                <template x-for="t in types" :key="t.id">
                    <button type="button" @click="serviceTypeId = t.id"
                            :class="serviceTypeId === t.id
                                ? 'ring-2 ring-brand-500 bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300'
                                : 'ring-1 ring-gray-200 dark:ring-white/10 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:ring-brand-300'"
                            class="rounded-full px-3 py-1.5 text-xs font-semibold flex items-center gap-1.5">
                        <span x-text="t.name"></span>
                        <span class="text-[10px] opacity-70" x-text="'(×' + t.multiplier + ')'"></span>
                    </button>
                </template>
            </div>
            <input type="hidden" name="service_type_id" :value="serviceTypeId">
        <?php else: ?>
            <input type="hidden" name="service_type_id" value="<?= (int) $selectedTypeId ?>">
        <?php endif; ?>

        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Services <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <template x-for="s in services" :key="s.id">
                <label :class="picked.includes(s.id) ? 'bg-brand-50 dark:bg-brand-500/15 ring-brand-300 dark:ring-brand-500/40' : 'bg-white dark:bg-gray-900 ring-gray-200 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5'"
                       class="flex items-start gap-3 rounded-lg ring-1 px-3 py-2.5 cursor-pointer transition">
                    <input type="checkbox" name="service_ids[]" :value="s.id" x-model="picked" class="mt-0.5 rounded border-gray-300 dark:border-white/10 text-brand-500 focus:ring-brand-500">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white text-sm" x-text="s.name"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <span x-text="s.duration"></span> min · LKR <span x-text="effectivePrice(s).toLocaleString()"></span>
                            <span class="ml-1 text-[10px] uppercase tracking-wide opacity-60" x-show="typeMultiplier() !== 1" x-text="'@ ×' + typeMultiplier()"></span>
                        </p>
                    </div>
                </label>
            </template>
        </div>
        <div x-show="picked.length" x-cloak class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            Total: <strong class="text-gray-900 dark:text-white" x-text="totalDuration + ' min · LKR ' + totalPrice.toLocaleString()"></strong>
        </div>
    </div>

    <!-- Step 3: Stylist -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex size-6 items-center justify-center rounded-full bg-brand-500 text-white text-xs font-bold">3</span>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Stylist</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
            <?php foreach ($staff as $st): ?>
                <label :class="staffId === <?= (int)$st['id'] ?> ? 'bg-brand-50 dark:bg-brand-500/15 ring-brand-300 dark:ring-brand-500/40' : 'bg-white dark:bg-gray-900 ring-gray-200 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5'"
                       class="flex flex-col items-center gap-1.5 rounded-lg ring-1 p-3 cursor-pointer transition">
                    <input type="radio" name="staff_id" value="<?= (int)$st['id'] ?>" @change="staffId = <?= (int)$st['id'] ?>; loadSlots()" <?= $isEdit && (int)$st['id'] === (int)$row['staff_id'] ? 'checked' : '' ?> class="sr-only">
                    <div class="size-10 rounded-full bg-gradient-to-br from-brand-400 to-pink-600 flex items-center justify-center text-white font-semibold"><?= esc(strtoupper(substr($st['full_name'], 0, 1))) ?></div>
                    <p class="text-xs font-medium text-gray-900 dark:text-white text-center truncate w-full"><?= esc($st['full_name']) ?></p>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Step 4: Date + slot -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex size-6 items-center justify-center rounded-full bg-brand-500 text-white text-xs font-bold">4</span>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Date &amp; available time</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Date</label>
                <input type="date" x-model="bookDate" @change="loadSlots()" :min="todayISO"
                       class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Time slot</label>
                <div x-show="loadingSlots" class="mt-2 text-sm text-gray-500 dark:text-gray-400 inline-flex items-center gap-2"><i data-lucide="loader-2" class="size-4 animate-spin"></i> Loading…</div>
                <p x-show="!loadingSlots && !slots.length && bookDate && staffId" x-cloak class="mt-2 text-sm text-gray-500 dark:text-gray-400">No slots available — try another day or stylist.</p>
                <p x-show="!loadingSlots && (!bookDate || !staffId || !picked.length)" x-cloak class="mt-2 text-sm text-gray-500 dark:text-gray-400">Choose services, stylist &amp; date to see slots.</p>
            </div>
        </div>

        <div x-show="!loadingSlots && slots.length" x-cloak class="mt-4 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-1.5">
            <template x-for="s in slots" :key="s.time">
                <button type="button" @click="if (s.available) bookSlot = s.time" :disabled="!s.available"
                        :class="!s.available ? 'bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-gray-600 cursor-not-allowed line-through' : (bookSlot === s.time ? 'bg-brand-500 text-white ring-2 ring-brand-300 dark:ring-brand-500/40' : 'bg-white dark:bg-white/5 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-white/10 hover:bg-brand-50 dark:hover:bg-brand-500/15')"
                        class="rounded-md px-2 py-2 text-xs font-mono font-semibold" x-text="s.time"></button>
            </template>
        </div>

        <input type="hidden" name="start_at" :value="bookDate && bookSlot ? bookDate + ' ' + bookSlot + ':00' : ''">
    </div>

    <!-- Status + notes -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow ring-1 ring-gray-200 dark:ring-white/10 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?= view('components/form/select',['name'=>'status','label'=>'Status','selected'=>$row['status'] ?? 'confirmed','options'=>['pending'=>'Pending','confirmed'=>'Confirmed','checked_in'=>'Checked in','in_progress'=>'In progress','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No show']]) ?>
        <?= view('components/form/textarea',['name'=>'notes','label'=>'Notes (optional)','rows'=>2,'value'=>$row['notes'] ?? '']) ?>
    </div>

    <!-- Submit -->
    <div class="sticky bottom-0 z-10 -mx-4 sm:-mx-6 lg:-mx-8 bg-gray-50/95 dark:bg-gray-950/95 backdrop-blur p-3 border-t border-gray-200 dark:border-white/10 flex items-center justify-end gap-3">
        <button type="submit" :disabled="!canSubmit"
                class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:bg-gray-300 dark:disabled:bg-white/10 disabled:text-gray-500 disabled:cursor-not-allowed">
            <i data-lucide="check" class="size-4"></i>
            <span><?= $isEdit ? 'Save changes' : 'Book appointment' ?></span>
        </button>
    </div>
</form>

<script>
function apptForm(cfg) {
    return {
        services: cfg.services,
        types: cfg.types,
        picked: [...(cfg.preselected || [])],
        staffId: cfg.initStaff || 0,
        bookDate: cfg.initDate || '',
        bookSlot: cfg.initSlot || '',
        serviceTypeId: cfg.initType || (cfg.types[0]?.id || null),
        slots: [],
        loadingSlots: false,

        get todayISO() { return new Date().toISOString().slice(0,10); },
        typeMultiplier() {
            const t = this.types.find(x => x.id === this.serviceTypeId);
            return t ? t.multiplier : 1;
        },
        effectivePrice(s) { return Math.round(s.price * this.typeMultiplier() * 100) / 100; },
        get totalDuration() { return this.services.filter(s=>this.picked.includes(s.id)).reduce((t,s)=>t+s.duration,0); },
        get totalPrice() {
            const m = this.typeMultiplier();
            return Math.round(this.services.filter(s=>this.picked.includes(s.id)).reduce((t,s)=>t+s.price,0) * m * 100) / 100;
        },
        get canSubmit() { return this.picked.length && this.staffId && this.bookDate && this.bookSlot; },

        init() {
            this.$watch('picked', () => this.loadSlots());
            if (this.staffId && this.bookDate && this.picked.length) this.loadSlots();
        },

        async loadSlots() {
            if (!this.staffId || !this.bookDate || !this.picked.length) { this.slots = []; return; }
            this.loadingSlots = true;
            try {
                const r = await fetch(cfg.availabilityUrl
                    + '?staff_id=' + this.staffId
                    + '&date=' + encodeURIComponent(this.bookDate)
                    + '&duration=' + this.totalDuration);
                const d = await r.json();
                if (d.ok) {
                    this.slots = d.slots || [];
                    if (this.bookSlot && !this.slots.find(s => s.time === this.bookSlot && s.available)) this.bookSlot = '';
                }
            } catch (e) {}
            this.loadingSlots = false;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },
    };
}
</script>
