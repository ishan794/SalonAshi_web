<?php
helper('saloncms');
$isEdit = !empty($row);
$action = $isEdit ? site_url('admin/customers/' . $row['id']) : site_url('admin/customers');

// Split the stored mobile back into country dial code + national number.
$split    = phone_split($row['mobile'] ?? '', '94');
$dialVal  = old('dial_code', $split['dial']);
$mobileVal = old('mobile', $split['national']);
$mobileErr = session('errors.mobile');
// Auto-detect the country from the browser timezone only on a fresh new form
// (not when editing, and not after a validation re-submit).
$autoDetect = (! $isEdit && ! old('dial_code')) ? 'true' : 'false';
?>
<form method="POST" action="<?= $action ?>" class="max-w-3xl space-y-6">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-5">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white"><?= $isEdit ? 'Edit' : 'New' ?> Customer</h3>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <?= view('components/form/input', ['name'=>'full_name','label'=>'Full name','required'=>true,'icon'=>'user','value'=>$row['full_name'] ?? '']) ?>

            <div>
                <label for="mobile" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Mobile <span class="text-red-500">*</span></label>
                <div x-data="phoneField('<?= esc($dialVal) ?>', '<?= esc($mobileVal) ?>', <?= $autoDetect ?>)" class="relative mt-2">
                    <div class="flex rounded-md bg-white dark:bg-white/5 outline-1 -outline-offset-1 <?= $mobileErr ? 'outline-red-400 dark:outline-red-500' : 'outline-gray-500 dark:outline-gray-500' ?> focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-brand-600 dark:focus-within:outline-brand-500">
                        <!-- Country trigger -->
                        <button type="button" @click="toggle()" :aria-expanded="open" aria-label="Select country"
                            class="flex shrink-0 items-center gap-1.5 rounded-l-md border-r border-gray-200 dark:border-white/10 pl-3 pr-2 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-white/5 focus:outline-none">
                            <span class="text-lg leading-none" x-text="current.flag"></span>
                            <span class="text-sm font-medium" x-text="'+' + dial"></span>
                            <svg class="size-4 text-gray-400 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                        </button>
                        <input type="hidden" name="dial_code" value="<?= esc($dialVal) ?>" :value="dial">
                        <input type="tel" name="mobile" id="mobile" required x-model="national"
                            placeholder="771234567" autocomplete="tel-national" inputmode="numeric"
                            class="block w-full min-w-0 grow rounded-r-md border-0 bg-transparent py-1.5 px-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6 dark:text-white dark:placeholder:text-gray-500">
                    </div>

                    <!-- Searchable dropdown -->
                    <div x-show="open" x-cloak x-transition.opacity.duration.100ms
                        @click.outside="open = false" @keydown.escape.window="open = false"
                        class="absolute left-0 z-30 mt-1 w-72 max-w-[88vw] overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10">
                        <div class="border-b border-gray-100 dark:border-white/10 p-2">
                            <input x-ref="search" x-model="q" type="text" placeholder="Search country or code…"
                                @keydown.enter.prevent="pickFirst()"
                                class="w-full rounded-md border-0 bg-gray-50 dark:bg-white/5 px-3 py-1.5 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                        <ul class="max-h-60 overflow-y-auto py-1">
                            <template x-for="c in filtered" :key="c.name">
                                <li @click="choose(c)"
                                    class="flex cursor-pointer items-center gap-2.5 px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-white/5"
                                    :class="c.dial === dial ? 'bg-brand-50 dark:bg-brand-500/10' : ''">
                                    <span class="text-lg leading-none" x-text="c.flag"></span>
                                    <span class="flex-1 truncate text-gray-900 dark:text-white" x-text="c.name"></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="'+' + c.dial"></span>
                                </li>
                            </template>
                            <li x-show="filtered.length === 0" class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400">No countries found.</li>
                        </ul>
                    </div>
                </div>
                <?php if ($mobileErr): ?>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= esc($mobileErr) ?></p>
                <?php else: ?>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter the number without the leading 0.</p>
                <?php endif; ?>
            </div>
            <?= view('components/form/input', ['name'=>'email','label'=>'Email','type'=>'email','icon'=>'mail','value'=>$row['email'] ?? '']) ?>
            <?= view('components/form/select', ['name'=>'gender','label'=>'Gender','selected'=>$row['gender'] ?? '','options'=>['' => '— Select —','male'=>'Male','female'=>'Female','other'=>'Other']]) ?>
            <?= view('components/form/input', ['name'=>'birthday','label'=>'Birthday','type'=>'date','value'=>$row['birthday'] ?? '']) ?>
            <?= view('components/form/select', ['name'=>'membership','label'=>'Membership','selected'=>$row['membership'] ?? 'none','options'=>['none'=>'None','silver'=>'Silver (5%)','gold'=>'Gold (10%)','platinum'=>'Platinum (15%)']]) ?>
        </div>

        <?= view('components/form/textarea', ['name'=>'address','label'=>'Address','rows'=>2,'value'=>$row['address'] ?? '']) ?>
        <?= view('components/form/textarea', ['name'=>'notes','label'=>'Notes / preferences','rows'=>2,'value'=>$row['notes'] ?? '']) ?>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="<?= site_url('admin/customers') ?>" class="rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5">Cancel</a>
        <button type="submit" class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700"><?= $isEdit ? 'Save changes' : 'Create customer' ?></button>
    </div>
</form>

<script>
/* Searchable country code picker with timezone auto-detect (Alpine.js). */
function phoneField(initialDial, initialNational, autoDetect) {
    const COUNTRIES = <?= json_encode(array_map(static fn ($c) => ['name' => $c['name'], 'dial' => $c['dial'], 'flag' => $c['flag']], country_dial_codes()), JSON_UNESCAPED_UNICODE) ?>;
    // Map common IANA timezones → dial code (covers the countries above).
    const TZ_DIAL = {
        'Asia/Colombo':'94','Asia/Kolkata':'91','Asia/Calcutta':'91','Asia/Dubai':'971','Asia/Riyadh':'966',
        'Asia/Qatar':'974','Asia/Kuwait':'965','Asia/Muscat':'968','Asia/Bahrain':'973','Europe/London':'44',
        'America/New_York':'1','America/Chicago':'1','America/Denver':'1','America/Los_Angeles':'1','America/Phoenix':'1',
        'America/Anchorage':'1','America/Toronto':'1','America/Vancouver':'1','America/Edmonton':'1','America/Halifax':'1',
        'Australia/Sydney':'61','Australia/Melbourne':'61','Australia/Brisbane':'61','Australia/Perth':'61','Australia/Adelaide':'61',
        'Asia/Singapore':'65','Asia/Kuala_Lumpur':'60','Indian/Maldives':'960','Asia/Karachi':'92','Asia/Dhaka':'880',
        'Asia/Kathmandu':'977','Asia/Shanghai':'86','Asia/Hong_Kong':'852','Asia/Tokyo':'81','Asia/Seoul':'82',
        'Asia/Bangkok':'66','Asia/Jakarta':'62','Asia/Manila':'63','Asia/Ho_Chi_Minh':'84','Asia/Saigon':'84',
        'Europe/Berlin':'49','Europe/Paris':'33','Europe/Rome':'39','Europe/Madrid':'34','Europe/Amsterdam':'31',
        'Europe/Zurich':'41','Europe/Stockholm':'46','Europe/Oslo':'47','Europe/Dublin':'353','Pacific/Auckland':'64',
        'Africa/Johannesburg':'27','Europe/Istanbul':'90','Europe/Moscow':'7','America/Sao_Paulo':'55'
    };
    return {
        countries: COUNTRIES,
        dial: String(initialDial || '94'),
        national: String(initialNational || ''),
        open: false,
        q: '',
        init() {
            if (autoDetect) {
                try {
                    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                    const d = TZ_DIAL[tz];
                    if (d && this.countries.some(c => c.dial === d)) this.dial = d;
                } catch (e) { /* keep default */ }
            }
        },
        get current() {
            return this.countries.find(c => c.dial === this.dial) || { flag: '🏳️', dial: this.dial, name: '' };
        },
        get filtered() {
            const s = this.q.trim().toLowerCase();
            if (!s) return this.countries;
            const sd = s.replace(/[^0-9]/g, '');
            return this.countries.filter(c =>
                c.name.toLowerCase().includes(s) || (sd && c.dial.includes(sd))
            );
        },
        toggle() {
            this.open = !this.open;
            if (this.open) { this.q = ''; this.$nextTick(() => this.$refs.search && this.$refs.search.focus()); }
        },
        choose(c) { this.dial = c.dial; this.open = false; this.q = ''; },
        pickFirst() { const f = this.filtered; if (f.length) this.choose(f[0]); },
    };
}
</script>
