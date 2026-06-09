<?php /** @var \App\Modules\Settings\Models\SettingModel $s */ ?>
<form method="POST" action="<?= site_url('admin/settings/gateways') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <!-- PayHere Card -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <i data-lucide="credit-card" class="size-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">PayHere</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Popular Sri Lankan payment gateway supporting LKR and USD.</p>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" name="payhere_enabled" value="1" <?= $s->get('payhere_enabled') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Enabled</span>
                </label>
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" name="payhere_sandbox" value="1" <?= $s->get('payhere_sandbox') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Sandbox</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <?= view('components/form/input',[
                'name'=>'payhere_merchant_id','label'=>'Merchant ID','placeholder'=>'e.g. 121xxxx',
                'value'=>old('payhere_merchant_id', $s->get('payhere_merchant_id'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'payhere_merchant_secret','label'=>'Merchant Secret','placeholder'=>'••••••••••••••••',
                'type'=>'password',
                'value'=>old('payhere_merchant_secret', $s->get('payhere_merchant_secret') ? '••••••••••••••••••••••••' : '')
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'payhere_app_id','label'=>'App ID','placeholder'=>'e.g. 4xxxxx',
                'value'=>old('payhere_app_id', $s->get('payhere_app_id'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'payhere_app_secret','label'=>'App Secret','placeholder'=>'••••••••••••••••',
                'type'=>'password',
                'value'=>old('payhere_app_secret', $s->get('payhere_app_secret') ? '••••••••••••••••••••••••' : '')
            ]) ?>
        </div>
    </div>

    <!-- OnePay Card -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <i data-lucide="wallet" class="size-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">OnePay</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Fast, local mobile-optimized payments in Sri Lanka.</p>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" name="onepay_enabled" value="1" <?= $s->get('onepay_enabled') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Enabled</span>
                </label>
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" name="onepay_sandbox" value="1" <?= $s->get('onepay_sandbox') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Sandbox</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <?= view('components/form/input',[
                'name'=>'onepay_merchant_id','label'=>'Merchant ID','placeholder'=>'e.g. merchant_xxxx',
                'value'=>old('onepay_merchant_id', $s->get('onepay_merchant_id'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'onepay_app_id','label'=>'App ID','placeholder'=>'e.g. app_xxxx',
                'value'=>old('onepay_app_id', $s->get('onepay_app_id'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'onepay_api_key','label'=>'API Key','placeholder'=>'••••••••••••••••',
                'type'=>'password',
                'value'=>old('onepay_api_key', $s->get('onepay_api_key') ? '••••••••••••••••••••••••' : '')
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'onepay_hash_salt','label'=>'Hash Salt / Secret','placeholder'=>'••••••••••••••••',
                'type'=>'password',
                'value'=>old('onepay_hash_salt', $s->get('onepay_hash_salt') ? '••••••••••••••••••••••••' : '')
            ]) ?>
        </div>
    </div>

    <!-- WebXPay Card -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <i data-lucide="shield-check" class="size-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">WebXPay</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Robust Sri Lankan e-commerce payment solution.</p>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" name="webxpay_enabled" value="1" <?= $s->get('webxpay_enabled') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Enabled</span>
                </label>
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" name="webxpay_sandbox" value="1" <?= $s->get('webxpay_sandbox') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Sandbox</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <?= view('components/form/input',[
                'name'=>'webxpay_merchant_id','label'=>'Merchant ID','placeholder'=>'e.g. Mxxxxxxx',
                'value'=>old('webxpay_merchant_id', $s->get('webxpay_merchant_id'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'webxpay_api_key','label'=>'API Key / Secret','placeholder'=>'••••••••••••••••',
                'type'=>'password',
                'value'=>old('webxpay_api_key', $s->get('webxpay_api_key') ? '••••••••••••••••••••••••' : '')
            ]) ?>
        </div>
    </div>

    <!-- Dialog Genie Card -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <i data-lucide="pocket" class="size-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Dialog Genie</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Fintech and mobile wallet payment gateway by Dialog Axiata Sri Lanka.</p>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" name="genie_enabled" value="1" <?= $s->get('genie_enabled') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Enabled</span>
                </label>
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" name="genie_sandbox" value="1" <?= $s->get('genie_sandbox') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Sandbox</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <?= view('components/form/input',[
                'name'=>'genie_merchant_id','label'=>'Merchant ID','placeholder'=>'e.g. genie_m_xxxx',
                'value'=>old('genie_merchant_id', $s->get('genie_merchant_id'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'genie_api_key','label'=>'API Key','placeholder'=>'••••••••••••••••',
                'type'=>'password',
                'value'=>old('genie_api_key', $s->get('genie_api_key') ? '••••••••••••••••••••••••' : '')
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'genie_merchant_secret','label'=>'Merchant Secret','placeholder'=>'••••••••••••••••',
                'type'=>'password',
                'value'=>old('genie_merchant_secret', $s->get('genie_merchant_secret') ? '••••••••••••••••••••••••' : '')
            ]) ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save Gateway Settings
        </button>
    </div>
</form>
