<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$slot    = (int) $s->get('appt_slot_interval', 15);
$defDur  = (int) $s->get('appt_default_duration', 30);
$dStart  = $s->get('appt_day_start', '09:00');
$dEnd    = $s->get('appt_day_end', '19:00');
$buffer  = (int) $s->get('appt_buffer_min', 0);
$lead    = (int) $s->get('appt_lead_min', 0);
$advance = (int) $s->get('appt_max_advance_days', 60);
$defStat = $s->get('appt_default_status', 'pending');
?>
<form method="POST" action="<?= site_url('admin/settings/appointments') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <!-- ── Duration & slots ── -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="calendar-clock" class="size-4 text-brand-500"></i> Duration &amp; time slots
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Defaults used by the booking wizard, POS and admin scheduler when offering times.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Default appointment duration</label>
                <div class="mt-1 flex items-center gap-2">
                    <input type="number" name="appt_default_duration" min="5" max="480" step="5" value="<?= esc($defDur) ?>"
                           class="w-32 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-sm text-gray-500 dark:text-gray-400">minutes</span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used when a service has no explicit duration set.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Time slot interval</label>
                <select name="appt_slot_interval" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <?php foreach ([5, 10, 15, 20, 30, 60] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $slot === $opt ? 'selected' : '' ?>>Every <?= $opt ?> minutes</option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Granularity of the bookable time-slot grid.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Booking day starts</label>
                <input type="time" name="appt_day_start" value="<?= esc($dStart) ?>"
                       class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Earliest slot offered (per-stylist schedules can override).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Booking day ends</label>
                <input type="time" name="appt_day_end" value="<?= esc($dEnd) ?>"
                       class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Last slot must finish by this time.</p>
            </div>
        </div>
    </div>

    <!-- ── Booking rules ── -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="shield-check" class="size-4 text-brand-500"></i> Booking rules
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Guard-rails applied when customers and staff create bookings.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Buffer between appointments</label>
                <div class="mt-1 flex items-center gap-2">
                    <input type="number" name="appt_buffer_min" min="0" max="120" step="5" value="<?= esc($buffer) ?>"
                           class="w-32 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-sm text-gray-500 dark:text-gray-400">minutes</span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Clean-up / turnaround time reserved after each booking.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Minimum lead time</label>
                <div class="mt-1 flex items-center gap-2">
                    <input type="number" name="appt_lead_min" min="0" step="15" value="<?= esc($lead) ?>"
                           class="w-32 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-sm text-gray-500 dark:text-gray-400">minutes</span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">How far ahead a slot must be to remain bookable (0 = allow same-minute).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Max advance booking window</label>
                <div class="mt-1 flex items-center gap-2">
                    <input type="number" name="appt_max_advance_days" min="0" step="1" value="<?= esc($advance) ?>"
                           class="w-32 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-sm text-gray-500 dark:text-gray-400">days</span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">How far into the future customers can book (0 = no limit).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Default status for new bookings</label>
                <select name="appt_default_status" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="pending"   <?= $defStat === 'pending'   ? 'selected' : '' ?>>Pending (needs confirmation)</option>
                    <option value="confirmed" <?= $defStat === 'confirmed' ? 'selected' : '' ?>>Confirmed (auto-accept)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Status assigned to bookings made from the public site.</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save appointment settings
        </button>
    </div>
</form>
