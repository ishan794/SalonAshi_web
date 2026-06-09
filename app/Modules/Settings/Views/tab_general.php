<?php /** @var \App\Modules\Settings\Models\SettingModel $s */ ?>
<form method="POST" action="<?= site_url('admin/settings/general') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">General</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Core identity & formatting used across invoices, emails and the dashboard.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <?= view('components/form/input',[
                'name'=>'salon_name','label'=>'Salon name','required'=>true,'icon'=>'scissors',
                'value'=>old('salon_name', $s->get('salon_name', 'SalonCMS Demo'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'salon_currency','label'=>'Currency code','required'=>true,'placeholder'=>'LKR',
                'helpText'=>'3-letter ISO code (LKR, USD, INR…)',
                'value'=>old('salon_currency', $s->get('salon_currency', 'LKR'))
            ]) ?>
            <?= view('components/form/select',[
                'name'=>'salon_timezone','label'=>'Timezone',
                'selected'=>old('salon_timezone', $s->get('salon_timezone', 'Asia/Colombo')),
                'options'=>[
                    'Asia/Colombo'=>'Asia/Colombo (Sri Lanka)',
                    'Asia/Kolkata'=>'Asia/Kolkata (India)',
                    'Asia/Dubai'=>'Asia/Dubai (UAE)',
                    'Asia/Singapore'=>'Asia/Singapore',
                    'Europe/London'=>'Europe/London (UK)',
                    'America/New_York'=>'America/New_York (US East)',
                    'America/Los_Angeles'=>'America/Los_Angeles (US West)',
                    'UTC'=>'UTC',
                ]
            ]) ?>
            <?= view('components/form/select',[
                'name'=>'salon_date_format','label'=>'Date format',
                'selected'=>old('salon_date_format', $s->get('salon_date_format', 'Y-m-d')),
                'options'=>[
                    'Y-m-d'=>'2026-05-25 (YYYY-MM-DD)',
                    'd/m/Y'=>'25/05/2026 (DD/MM/YYYY)',
                    'm/d/Y'=>'05/25/2026 (MM/DD/YYYY)',
                    'd M Y'=>'25 May 2026',
                    'M j, Y'=>'May 25, 2026',
                ]
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'salon_tax_pct','label'=>'Default tax %','type'=>'number','attrs'=>['step'=>'0.01'],
                'helpText'=>'Applied as a default on new services',
                'value'=>old('salon_tax_pct', $s->get('salon_tax_pct', '0'))
            ]) ?>
            <div></div>
            <?= view('components/form/input',[
                'name'=>'salon_invoice_prefix','label'=>'Invoice number prefix','placeholder'=>'INV-',
                'value'=>old('salon_invoice_prefix', $s->get('salon_invoice_prefix', 'INV-'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'salon_appt_prefix','label'=>'Appointment code prefix','placeholder'=>'APT-',
                'value'=>old('salon_appt_prefix', $s->get('salon_appt_prefix', 'APT-'))
            ]) ?>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save general settings
        </button>
    </div>
</form>
