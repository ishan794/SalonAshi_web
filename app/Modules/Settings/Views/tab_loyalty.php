<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$enabled = (string) $s->get('loyalty_enabled', '1') === '1';
?>
<form method="POST" action="<?= site_url('admin/settings/loyalty') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <!-- Toggle -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="loyalty_enabled" value="1" <?= $enabled ? 'checked' : '' ?>
                   class="mt-1 size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:checked:bg-brand-500 dark:checked:border-brand-500">
            <span class="flex-1">
                <span class="block text-base font-semibold text-gray-900 dark:text-white">Enable loyalty program</span>
                <span class="block text-sm text-gray-500 dark:text-gray-400">Customers earn points on paid invoices and can redeem them for discount.</span>
            </span>
        </label>
    </div>

    <!-- Earn / Redeem rates -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Earn &amp; redeem rates</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Defaults: 1 point per 100 LKR spent · 1 point = 0.5 LKR discount · 50 pts minimum redemption.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <?= view('components/form/input',[
                'name'=>'loyalty_earn_per_lkr','label'=>'Points per LKR earned','type'=>'number','attrs'=>['step'=>'0.001'],
                'helpText'=>'e.g. 0.01 = 1 pt per 100 LKR',
                'value'=>old('loyalty_earn_per_lkr', $s->get('loyalty_earn_per_lkr','0.01'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'loyalty_redeem_value','label'=>'LKR per point on redeem','type'=>'number','attrs'=>['step'=>'0.01'],
                'helpText'=>'e.g. 0.5 = 1 pt worth 0.5 LKR off',
                'value'=>old('loyalty_redeem_value', $s->get('loyalty_redeem_value','0.5'))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'loyalty_min_redeem_pts','label'=>'Minimum points to redeem','type'=>'number',
                'value'=>old('loyalty_min_redeem_pts', $s->get('loyalty_min_redeem_pts','50'))
            ]) ?>
        </div>
        <div class="mt-4 rounded-md bg-brand-50 dark:bg-brand-500/10 p-3 ring-1 ring-brand-200 dark:ring-brand-500/20 text-xs text-gray-700 dark:text-gray-300">
            <p><strong>Worked example:</strong>
                Customer pays <code class="text-brand-700 dark:text-brand-300">LKR 5,000</code> →
                earns <code class="text-brand-700 dark:text-brand-300"><?= (int) floor(5000 * (float)$s->get('loyalty_earn_per_lkr', '0.01')) ?> points</code>.
                100 points = <code class="text-brand-700 dark:text-brand-300">LKR <?= number_format(100 * (float)$s->get('loyalty_redeem_value', '0.5'), 0) ?></code> discount.
            </p>
        </div>
    </div>

    <!-- Tier thresholds -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Membership tiers</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Customers auto-upgrade based on <em>lifetime earned points</em>.</p>
        </div>

        <div class="space-y-3">
            <?php foreach (['silver' => ['Silver','slate'], 'gold' => ['Gold','amber'], 'platinum' => ['Platinum','purple']] as $tier => [$label, $color]): ?>
                <div class="grid grid-cols-12 gap-3 items-end rounded-md bg-<?= $color ?>-50 dark:bg-<?= $color ?>-500/10 ring-1 ring-<?= $color ?>-200 dark:ring-<?= $color ?>-500/20 px-4 py-3">
                    <div class="col-span-4 flex items-center gap-2">
                        <span class="inline-flex size-8 items-center justify-center rounded-full bg-<?= $color ?>-100 dark:bg-<?= $color ?>-500/20 text-<?= $color ?>-700 dark:text-<?= $color ?>-300">
                            <i data-lucide="award" class="size-4"></i>
                        </span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($label) ?></span>
                    </div>
                    <div class="col-span-4">
                        <label class="block text-[10px] uppercase tracking-wide font-medium text-gray-600 dark:text-gray-400">Lifetime pts threshold</label>
                        <input type="number" name="loyalty_tier_<?= $tier ?>_pts" value="<?= esc($s->get('loyalty_tier_'.$tier.'_pts', '0')) ?>"
                               class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div class="col-span-4">
                        <label class="block text-[10px] uppercase tracking-wide font-medium text-gray-600 dark:text-gray-400">Tier discount % (info only)</label>
                        <input type="number" step="0.01" name="loyalty_tier_<?= $tier ?>_disc" value="<?= esc($s->get('loyalty_tier_'.$tier.'_disc', '0')) ?>"
                               class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save loyalty settings
        </button>
    </div>
</form>
