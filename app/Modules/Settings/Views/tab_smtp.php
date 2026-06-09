<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$hasPass = !empty($s->get('smtp_pass'));
?>
<div class="space-y-6">
    <form method="POST" action="<?= site_url('admin/settings/smtp') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">SMTP server</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Used to send appointment reminders, invoices, and password resets.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <?= view('components/form/input',[
                        'name'=>'smtp_host','label'=>'SMTP host','placeholder'=>'smtp.gmail.com',
                        'icon'=>'server',
                        'value'=>old('smtp_host', $s->get('smtp_host',''))
                    ]) ?>
                </div>
                <?= view('components/form/input',[
                    'name'=>'smtp_port','label'=>'Port','type'=>'number','placeholder'=>'587',
                    'value'=>old('smtp_port', $s->get('smtp_port','587'))
                ]) ?>
                <?= view('components/form/select',[
                    'name'=>'smtp_encryption','label'=>'Encryption',
                    'selected'=>old('smtp_encryption', $s->get('smtp_encryption','tls')),
                    'options'=>['' => 'None', 'tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)']
                ]) ?>
                <?= view('components/form/input',[
                    'name'=>'smtp_user','label'=>'Username','icon'=>'user',
                    'autocomplete'=>'off',
                    'value'=>old('smtp_user', $s->get('smtp_user',''))
                ]) ?>
                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Password</label>
                    <div class="mt-2 relative">
                        <i data-lucide="lock" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 dark:text-gray-500"></i>
                        <input type="password" name="smtp_pass" autocomplete="new-password"
                               placeholder="<?= $hasPass ? '•••••••• (leave blank to keep)' : 'Enter password' ?>"
                               class="block w-full rounded-md bg-white pl-10 pr-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?= $hasPass ? 'A password is currently saved. Leave blank to keep it.' : 'For Gmail, generate an App Password.' ?></p>
                </div>
                <?= view('components/form/input',[
                    'name'=>'smtp_from_email','label'=>'From email','type'=>'email','icon'=>'at-sign',
                    'placeholder'=>'noreply@yoursalon.com',
                    'value'=>old('smtp_from_email', $s->get('smtp_from_email',''))
                ]) ?>
                <?= view('components/form/input',[
                    'name'=>'smtp_from_name','label'=>'From name','icon'=>'user-round',
                    'placeholder'=>$s->get('salon_name','SalonCMS'),
                    'value'=>old('smtp_from_name', $s->get('smtp_from_name',''))
                ]) ?>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
                <i data-lucide="check" class="size-4"></i> Save SMTP settings
            </button>
        </div>
    </form>

    <!-- ── Send a test ── -->
    <form method="POST" action="<?= site_url('admin/settings/smtp/test') ?>"
          class="rounded-lg bg-gradient-to-br from-brand-50 to-pink-50 dark:from-brand-500/10 dark:to-purple-500/10 p-6 ring-1 ring-brand-200 dark:ring-brand-500/20">
        <?= csrf_field() ?>
        <div class="flex items-start gap-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                <i data-lucide="send" class="size-5"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Send a test email</h4>
                <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">Verify your settings work without leaving this page.</p>
                <div class="mt-3 flex flex-col sm:flex-row gap-2">
                    <input type="email" name="test_to" required placeholder="you@example.com"
                           class="flex-1 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700">
                        <i data-lucide="paper-plane" class="size-4"></i> Send test
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
