<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$base = rtrim(base_url(), '/');
$crontabSnippet = <<<CRON
# SalonCMS — appointment reminders (checks every 15 min, emails inside the lead window)
*/15 * * * * cd /var/www/saloncms && /usr/bin/php spark reminders:send >> writable/logs/reminders.log 2>&1
CRON;

$tasks = [
    [
        'key'  => 'cron_last_reminders',
        'name' => 'Appointment reminders',
        'desc' => 'Email/SMS reminder N hours before each appointment',
        'icon' => 'bell-ring',
        'status' => $s->get('cron_last_reminders') ? 'last run ' . $s->get('cron_last_reminders') : 'never run',
    ],
    [
        'key'  => 'cron_last_daily_report',
        'name' => 'Daily summary report',
        'desc' => 'End-of-day revenue + appointments digest to owner email',
        'icon' => 'file-bar-chart',
        'status' => $s->get('cron_last_daily_report') ? 'last run ' . $s->get('cron_last_daily_report') : 'never run',
    ],
    [
        'key'  => 'cron_last_backup',
        'name' => 'Database backup',
        'desc' => 'mysqldump of saloncms DB to /var/www/saloncms/writable/backups/',
        'icon' => 'database-backup',
        'status' => $s->get('cron_last_backup') ? 'last run ' . $s->get('cron_last_backup') : 'never run',
    ],
    [
        'key'  => 'cron_last_loyalty',
        'name' => 'Loyalty / birthday emails',
        'desc' => 'Send birthday vouchers and tier upgrades each morning',
        'icon' => 'gift',
        'status' => $s->get('cron_last_loyalty') ? 'last run ' . $s->get('cron_last_loyalty') : 'never run',
    ],
];
?>
<div class="space-y-6">
    <!-- Settings form -->
    <form method="POST" action="<?= site_url('admin/settings/cron') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Schedule preferences</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">When each scheduled task should fire.</p>
            </div>
            <label class="flex items-start gap-3 rounded-md ring-1 ring-gray-200 dark:ring-white/10 p-3 mb-5 hover:bg-gray-50 dark:hover:bg-white/5">
                <input type="checkbox" name="reminders_enabled" value="1" <?= $s->get('reminders_enabled')==='1'?'checked':'' ?> class="mt-1 rounded border-gray-300 dark:border-white/10 text-brand-500 focus:ring-brand-500">
                <span>
                    <span class="block text-sm font-semibold text-gray-900 dark:text-white">Enable appointment reminders</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">When on, the <code>reminders:send</code> cron emails customers before their appointment. Requires working SMTP + the crontab line below.</span>
                </span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <?= view('components/form/input',[
                    'name'=>'cron_reminder_hours_before','label'=>'Reminder lead time (hours)','type'=>'number',
                    'helpText'=>'Send appointment reminder this many hours before start',
                    'value'=>old('cron_reminder_hours_before', $s->get('cron_reminder_hours_before','24'))
                ]) ?>
                <?= view('components/form/input',[
                    'name'=>'cron_daily_report_time','label'=>'Daily report time','type'=>'time',
                    'value'=>old('cron_daily_report_time', $s->get('cron_daily_report_time','20:00'))
                ]) ?>
                <label class="flex items-start gap-3 rounded-md ring-1 ring-gray-200 dark:ring-white/10 p-3 sm:col-span-2 hover:bg-gray-50 dark:hover:bg-white/5">
                    <input type="checkbox" name="cron_backup_enabled" value="1" <?= $s->get('cron_backup_enabled')==='1'?'checked':'' ?>
                           class="mt-0.5 size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span>
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Enable nightly database backup</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Runs at 03:00 local time. Keeps last 14 days under <code class="text-brand-600 dark:text-brand-400">writable/backups/</code>.</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
                <i data-lucide="check" class="size-4"></i> Save cron settings
            </button>
        </div>
    </form>

    <!-- Task list -->
    <div class="rounded-lg bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Scheduled tasks</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">These tasks are dispatched by the spark scheduler.</p>
        </div>
        <ul class="divide-y divide-gray-100 dark:divide-white/5">
            <?php foreach ($tasks as $t): ?>
                <li class="px-6 py-4 flex items-start gap-4 hover:bg-gray-50 dark:hover:bg-white/5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                        <i data-lucide="<?= esc($t['icon']) ?>" class="size-5"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($t['name']) ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($t['desc']) ?></p>
                    </div>
                    <span class="shrink-0 inline-flex items-center rounded-full bg-gray-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                        <?= esc($t['status']) ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Crontab snippet -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10"
         x-data="{ copied: false, copy() { navigator.clipboard.writeText(this.$refs.snip.textContent.trim()); this.copied = true; setTimeout(()=>this.copied=false, 1800); } }">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Install on the server</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">SSH into the server, run <code class="text-brand-600 dark:text-brand-400">crontab -e</code> as <code>www-data</code> or root, and add this line. It runs every 5 minutes — the framework itself decides which jobs to actually fire.</p>
            </div>
            <button type="button" @click="copy()" class="shrink-0 inline-flex items-center gap-1.5 rounded-md bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                <i data-lucide="copy" class="size-3.5"></i>
                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
            </button>
        </div>
        <pre x-ref="snip" class="mt-4 overflow-x-auto rounded-md bg-gray-900 dark:bg-black/60 p-4 text-xs leading-relaxed text-gray-100"><?= esc($crontabSnippet) ?></pre>

        <div class="mt-4 rounded-md bg-amber-50 dark:bg-amber-500/10 p-3 ring-1 ring-amber-200 dark:ring-amber-500/30">
            <div class="flex gap-2">
                <i data-lucide="info" class="size-4 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0"></i>
                <p class="text-xs text-amber-800 dark:text-amber-200">
                    The actual task runners (reminders, backups, daily report) ship as no-ops in Phase 1.
                    Set this cron up now and the jobs will start firing once Phase 2 enables them.
                </p>
            </div>
        </div>
    </div>
</div>
