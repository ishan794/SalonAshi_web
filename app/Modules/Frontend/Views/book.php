<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data);
$preselectId = (int) ($_GET['service_id'] ?? 0);
?>
<section class="pt-28 pb-16 bg-transparent min-h-screen relative isolate overflow-hidden">
    <!-- Decorative Interior Watermark -->
    <img src="<?= base_url('uploads/booking-bg.jpg') ?>" alt="" class="absolute top-0 right-0 w-full h-full object-contain object-right opacity-5 grayscale invert mix-blend-screen pointer-events-none -z-10">

    <div class="mx-auto max-w-4xl px-6 lg:px-8 relative z-10">

        <div class="text-center mb-12" data-aos="fade-down">
            <h1 class="mt-6 text-4xl lg:text-5xl font-bold tracking-widest text-white font-display uppercase"><?= lang('Site.book.heading') ?></h1>
            <p class="mt-3 text-gray-400 font-light"><?= lang('Site.book.lead') ?></p>
            <div class="mt-6 inline-flex items-center gap-3 bg-brand-500/10 border border-brand-500/50 px-6 py-4 text-brand-400 text-sm font-display tracking-widest rounded-none shadow-sm">
                <i data-lucide="info" class="size-4"></i>
                <?= lang('Site.book.flow_hint') ?? 'Please select your services and stylist first to check availability' ?>
            </div>
        </div>

        <form method="POST" action="<?= site_url('book') ?>" class="space-y-8" data-aos="fade-up" data-aos-delay="200"
              x-data='bookFlow({
                  services: <?= json_encode(array_values(array_map(fn($s)=>["id"=>(int)$s["id"],"name"=>$s["name"],"duration"=>(int)$s["duration_min"],"price"=>(float)$s["price"]], $services)), JSON_UNESCAPED_UNICODE) ?>,
                  staff:    <?= json_encode(array_values(array_map(fn($s)=>["id"=>(int)$s["id"],"name"=>$s["full_name"],"role"=>$s["role"] ?: lang("Site.team.role")], $staff)), JSON_UNESCAPED_UNICODE) ?>,
                  preselect: <?= (int) $preselectId ?>
              })'>
            <?= csrf_field() ?>

            <!-- Step 1: Services -->
            <div class="bg-zinc-900/80 p-6 md:p-8 border border-brand-500/20 shadow-2xl">
                <div class="flex items-center gap-4 mb-6 pb-4 border-b border-brand-500/10">
                    <span class="flex size-10 items-center justify-center bg-brand-500 text-zinc-950 text-xl font-bold font-display">1</span>
                    <h2 class="text-2xl font-bold text-white font-display tracking-wider uppercase"><?= lang('Site.book.step_services') ?></h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-for="s in services" :key="s.id">
                        <label :class="picked.includes(s.id) ? 'bg-brand-500/10 border-brand-500' : 'bg-zinc-950 border-white/10 hover:border-brand-500/50 hover:bg-zinc-800'"
                               class="flex items-start gap-4 border p-4 cursor-pointer transition-colors duration-300">
                            <input type="checkbox" name="service_ids[]" :value="s.id" x-model="picked" class="mt-1 rounded-none border-white/20 bg-transparent text-brand-500 focus:ring-brand-500 focus:ring-offset-zinc-950">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold font-display tracking-widest uppercase text-white" x-text="s.name"></p>
                                <p class="text-sm text-brand-400 mt-1"><span x-text="s.duration"></span> <?= lang('Site.services.minutes') ?> &mdash; LKR <span x-text="s.price.toLocaleString()"></span></p>
                            </div>
                        </label>
                    </template>
                </div>
                <div x-show="picked.length" x-cloak class="mt-6 p-4 bg-zinc-950 border border-brand-500/20 text-sm text-gray-400 flex justify-between items-center font-display tracking-widest uppercase">
                    <span><?= lang('Site.book.total') ?></span>
                    <strong class="text-brand-400 text-lg" x-text="totalDuration + ' <?= lang('Site.services.minutes') ?> / LKR ' + totalPrice.toLocaleString()"></strong>
                </div>
            </div>

            <!-- Step 2: Stylist -->
            <div class="bg-zinc-900/80 p-6 md:p-8 border border-brand-500/20 shadow-2xl">
                <div class="flex items-center gap-4 mb-6 pb-4 border-b border-brand-500/10">
                    <span class="flex size-10 items-center justify-center bg-brand-500 text-zinc-950 text-xl font-bold font-display">2</span>
                    <h2 class="text-2xl font-bold text-white font-display tracking-wider uppercase"><?= lang('Site.book.step_stylist') ?></h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <template x-for="st in staff" :key="st.id">
                        <label :class="staffId === st.id ? 'bg-brand-500/10 border-brand-500' : 'bg-zinc-950 border-white/10 hover:border-brand-500/50 hover:bg-zinc-800'"
                               class="flex flex-col items-center gap-3 border p-5 cursor-pointer transition-colors duration-300 group">
                            <input type="radio" name="staff_id" :value="st.id" x-model.number="staffId" class="sr-only">
                            <div class="size-16 bg-zinc-800 flex items-center justify-center text-brand-500 font-bold font-display text-2xl group-hover:bg-brand-500 group-hover:text-zinc-950 transition-colors" x-text="st.name.charAt(0).toUpperCase()"></div>
                            <p class="text-sm font-bold tracking-widest font-display uppercase text-white text-center" x-text="st.name"></p>
                            <p class="text-xs text-brand-400 font-display tracking-wider uppercase" x-text="st.role"></p>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Step 3: Date + time -->
            <div class="bg-zinc-900/80 p-6 md:p-8 border border-brand-500/20 shadow-2xl">
                <div class="flex items-center gap-4 mb-6 pb-4 border-b border-brand-500/10">
                    <span class="flex size-10 items-center justify-center bg-brand-500 text-zinc-950 text-xl font-bold font-display">3</span>
                    <h2 class="text-2xl font-bold text-white font-display tracking-wider uppercase"><?= lang('Site.book.step_datetime') ?></h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.book.date_label') ?></label>
                        <div x-data='datepicker({ value: bookDate, min: todayISO })'
                             x-init="$watch('value', v => { bookDate = v; loadSlots(); })"
                             @keydown.escape.window="open = false"
                             @click.outside="open = false"
                             class="relative">
                            <button type="button" @click="toggle()" class="flex w-full items-center gap-3 bg-zinc-950 border border-white/10 hover:border-brand-500/50 py-3 px-4 text-left text-sm text-white transition-colors focus:outline-none focus:border-brand-500">
                                <i data-lucide="calendar" class="size-5 text-brand-500"></i>
                                <span class="flex-1 truncate font-display tracking-widest uppercase text-base" x-text="value ? formatted() : '<?= esc(lang('Site.book.pick_date'), 'js') ?>'" :class="!value && 'text-gray-500'"></span>
                                <i data-lucide="chevron-down" class="size-5 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-cloak x-transition class="absolute z-50 mt-2 w-full origin-top rounded-none bg-zinc-800 p-4 shadow-2xl border border-brand-500/30">
                                <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
                                    <button type="button" @click="prevMonth()" class="inline-flex size-8 items-center justify-center text-gray-400 hover:text-white hover:bg-zinc-700 transition-colors"><i data-lucide="chevron-left" class="size-5"></i></button>
                                    <button type="button" @click="goToToday()" class="text-base tracking-widest font-bold font-display text-white hover:text-brand-500 uppercase" x-text="monthLabel()"></button>
                                    <button type="button" @click="nextMonth()" class="inline-flex size-8 items-center justify-center text-gray-400 hover:text-white hover:bg-zinc-700 transition-colors"><i data-lucide="chevron-right" class="size-5"></i></button>
                                </div>
                                <div class="grid grid-cols-7 text-center text-xs font-bold font-display uppercase tracking-widest text-brand-400 pb-2"><div>S</div><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div></div>
                                <div class="grid grid-cols-7 gap-2">
                                    <template x-for="(c, i) in cells()" :key="i">
                                        <button type="button" x-show="c" @click="pick(c)" :disabled="isDisabled(c)" :class="cellClass(c) + ' aspect-square text-sm font-display transition-colors'" x-text="c ? +c.split('-')[2] : ''"></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" :value="bookDate" name="_book_date">
                    </div>
                    <div>
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.book.time_label') ?></label>
                        <div x-show="loadingSlots" class="mt-4 text-sm font-display tracking-widest text-brand-400 uppercase inline-flex items-center gap-3"><i data-lucide="loader-2" class="size-5 animate-spin"></i> <?= lang('Site.book.loading_slots') ?></div>
                        <p x-show="!loadingSlots && !slots.length && bookDate && staffId" x-cloak class="mt-4 text-sm font-display tracking-widest uppercase text-red-400"><?= lang('Site.book.no_slots') ?></p>
                        <p x-show="!loadingSlots && (!bookDate || !staffId || !picked.length)" x-cloak class="mt-4 text-sm font-display tracking-widest uppercase text-gray-500"><?= lang('Site.book.fill_first') ?></p>
                    </div>
                </div>

                <div x-show="!loadingSlots && slots.length" x-cloak class="mt-6 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 border-t border-brand-500/10 pt-6">
                    <template x-for="s in slots" :key="s.time">
                        <button type="button" @click="if (s.available) bookSlot = s.time" :disabled="!s.available"
                                :class="!s.available ? 'bg-zinc-950 text-gray-700 cursor-not-allowed border border-white/5 line-through' : (bookSlot === s.time ? 'bg-brand-500 text-zinc-950 border-brand-500 shadow-lg shadow-brand-500/20' : 'bg-zinc-950 text-white border border-white/10 hover:border-brand-500 hover:text-brand-400')"
                                class="px-2 py-3 text-sm font-display tracking-widest transition-colors duration-300" x-text="s.time"></button>
                    </template>
                </div>

                <input type="hidden" name="start_at" :value="bookDate && bookSlot ? bookDate + ' ' + bookSlot + ':00' : ''">
            </div>

            <!-- Step 4: Contact info -->
            <div class="bg-zinc-900/80 p-6 md:p-8 border border-brand-500/20 shadow-2xl">
                <div class="flex items-center gap-4 mb-6 pb-4 border-b border-brand-500/10">
                    <span class="flex size-10 items-center justify-center bg-brand-500 text-zinc-950 text-xl font-bold font-display">4</span>
                    <h2 class="text-2xl font-bold text-white font-display tracking-wider uppercase"><?= lang('Site.book.step_details') ?></h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.book.full_name') ?> <span class="text-brand-500">*</span></label>
                        <input name="full_name" required class="w-full bg-zinc-950 border border-white/10 text-white text-base py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans">
                    </div>
                    <div>
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.book.mobile') ?> <span class="text-brand-500">*</span></label>
                        <input name="mobile" required placeholder="<?= esc(lang('Site.book.mobile_hint')) ?>" class="w-full bg-zinc-950 border border-white/10 text-white text-base py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans placeholder-gray-600">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-display tracking-widest uppercase text-gray-400 mb-2"><?= lang('Site.book.email_opt') ?></label>
                        <input name="email" type="email" class="w-full bg-zinc-950 border border-white/10 text-white text-base py-3 px-4 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-sans">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="text-center sticky bottom-6 z-30 bg-zinc-900/90 backdrop-blur-md py-4 border border-brand-500/20 shadow-2xl">
                <button type="submit" :disabled="!canSubmit"
                        class="inline-flex items-center gap-3 bg-brand-500 px-10 py-4 text-lg font-bold font-display uppercase tracking-widest text-zinc-950 shadow-2xl shadow-brand-500/30 hover:bg-brand-600 transition-colors disabled:bg-zinc-800 disabled:text-gray-500 disabled:cursor-not-allowed disabled:shadow-none border border-transparent disabled:border-white/10">
                    <i data-lucide="calendar-check-2" class="size-6"></i>
                    <?= lang('Site.book.submit') ?>
                </button>
                <p x-show="!canSubmit" x-cloak class="mt-3 text-sm font-display tracking-widest uppercase text-brand-400"><?= lang('Site.book.completion_hint') ?></p>
            </div>
        </form>
    </div>
</section>

<script>
function bookFlow(cfg) {
    return {
        services: cfg.services,
        staff: cfg.staff,
        picked: cfg.preselect ? [cfg.preselect] : [],
        staffId: 0,
        bookDate: '',
        bookSlot: '',
        slots: [],
        loadingSlots: false,

        get todayISO() { return new Date().toISOString().slice(0,10); },
        get totalDuration() { return this.services.filter(s=>this.picked.includes(s.id)).reduce((t,s)=>t+s.duration,0); },
        get totalPrice() { return this.services.filter(s=>this.picked.includes(s.id)).reduce((t,s)=>t+s.price,0); },
        get canSubmit() { return this.picked.length && this.staffId && this.bookDate && this.bookSlot; },

        init() {
            this.$watch('picked', () => this.loadSlots());
            this.$watch('staffId', () => this.loadSlots());
        },

        async loadSlots() {
            if (!this.staffId || !this.bookDate || !this.picked.length) { this.slots = []; return; }
            this.loadingSlots = true;
            try {
                const r = await fetch('<?= site_url('admin/pos/availability') ?>'
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
            this.$nextTick(renderIcons);
        },
    };
}
</script>
