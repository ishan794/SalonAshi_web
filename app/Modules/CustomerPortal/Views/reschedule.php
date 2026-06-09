<?php
/** @var \App\Modules\Settings\Models\SettingModel $s, array $data */
extract($data); // appt, duration
?>
<section class="pt-28 pb-10 bg-gradient-to-br from-amber-50 to-white dark:from-gray-950 dark:to-gray-950 dark:bg-gray-950 dark:bg-none">
    <div class="mx-auto max-w-2xl px-6 lg:px-8">
        <a href="<?= site_url('portal/dashboard') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:text-brand-600">
            <i data-lucide="arrow-left" class="size-4"></i> <?= lang('Site.portal.back_to_account') ?>
        </a>
        <h1 class="mt-3 text-2xl lg:text-3xl font-bold tracking-tight text-gray-900 dark:text-white font-display"><?= lang('Site.reschedule.heading') ?></h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?= lang('Site.reschedule.current', [esc(date('l, M j \a\t H:i', strtotime($appt['start_at'])))]) ?></p>
    </div>
</section>

<section class="py-8 lg:py-12 bg-white dark:bg-gray-950">
    <div class="mx-auto max-w-2xl px-6 lg:px-8">
        <?php if (session('flash_error')): ?>
            <div class="mb-5 rounded-lg bg-red-50 dark:bg-red-500/10 p-4 ring-1 ring-red-200 dark:ring-red-500/30 text-sm text-red-800 dark:text-red-300"><?= esc(session('flash_error')) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= site_url('portal/booking/' . (int)$appt['id'] . '/reschedule') ?>"
              class="rounded-2xl bg-white dark:bg-gray-900 p-6 ring-1 ring-gray-200 dark:ring-white/10 shadow-sm space-y-5"
              x-data='reschedule({ staffId: <?= (int)$appt['staff_id'] ?>, duration: <?= (int)$duration ?>, url: "<?= site_url('portal/availability') ?>" })'>
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white"><?= lang('Site.book.date_label') ?></label>
                <input type="date" x-model="date" @change="load()" :min="todayISO"
                       class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white"><?= lang('Site.book.time_label') ?></label>
                <div x-show="loading" class="mt-2 text-sm text-gray-500 dark:text-gray-400 inline-flex items-center gap-2"><i data-lucide="loader-2" class="size-4 animate-spin"></i> <?= lang('Site.book.loading_slots') ?></div>
                <p x-show="!loading && !slots.length && date" x-cloak class="mt-2 text-sm text-gray-500 dark:text-gray-400"><?= lang('Site.book.no_slots') ?></p>
                <div x-show="!loading && slots.length" x-cloak class="mt-3 grid grid-cols-4 sm:grid-cols-6 gap-1.5">
                    <template x-for="sl in slots" :key="sl.time">
                        <button type="button" @click="if(sl.available) slot = sl.time" :disabled="!sl.available"
                                :class="!sl.available ? 'bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-gray-600 cursor-not-allowed line-through' : (slot===sl.time ? 'bg-brand-500 text-white ring-2 ring-brand-300' : 'bg-white dark:bg-white/5 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-white/10 hover:bg-brand-50 dark:hover:bg-brand-500/15')"
                                class="rounded-md px-2 py-2 text-xs font-mono font-semibold" x-text="sl.time"></button>
                    </template>
                </div>
            </div>
            <input type="hidden" name="start_at" :value="date && slot ? date + ' ' + slot + ':00' : ''">
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-white/10">
                <a href="<?= site_url('portal/dashboard') ?>" class="rounded-md bg-gray-100 dark:bg-white/5 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Cancel</a>
                <button type="submit" :disabled="!date || !slot" class="inline-flex items-center gap-1.5 rounded-md bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 disabled:bg-gray-300 dark:disabled:bg-white/10 disabled:text-gray-500 disabled:cursor-not-allowed">
                    <i data-lucide="check" class="size-4"></i> <?= lang('Site.reschedule.confirm') ?>
                </button>
            </div>
        </form>
    </div>
</section>

<script>
function reschedule(cfg) {
    return {
        staffId: cfg.staffId, duration: cfg.duration, url: cfg.url,
        date: '', slot: '', slots: [], loading: false,
        get todayISO() { return new Date().toISOString().slice(0,10); },
        async load() {
            if (!this.date) { this.slots = []; return; }
            this.loading = true;
            try {
                const r = await fetch(this.url + '?staff_id=' + this.staffId + '&date=' + encodeURIComponent(this.date) + '&duration=' + this.duration, { credentials: 'same-origin' });
                const d = await r.json();
                this.slots = d.slots || [];
                if (this.slot && !this.slots.find(s => s.time === this.slot && s.available)) this.slot = '';
            } catch (e) {}
            this.loading = false;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        }
    };
}
</script>
