<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$logo    = $s->get('biz_logo');
$favicon = $s->get('biz_favicon');
?>
<form method="POST" action="<?= site_url('admin/settings/business') ?>" enctype="multipart/form-data" class="space-y-6">
    <?= csrf_field() ?>

    <!-- ── Logo & Favicon ── -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Branding</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Logo appears on invoices, receipts and the public booking page. Favicon shows in the browser tab.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Logo -->
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Salon logo</label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG / JPG / SVG / WebP — max 2 MB. Recommended 400×120.</p>
                <div class="mt-3 flex items-center gap-4">
                    <div class="flex h-20 w-32 items-center justify-center rounded-md bg-gray-50 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10 overflow-hidden">
                        <img id="logoPreview" src="<?= $logo ? base_url('uploads/' . $logo) : '' ?>" alt="Logo" class="max-h-full max-w-full object-contain <?= $logo ? '' : 'hidden' ?>">
                        <i id="logoPlaceholder" data-lucide="image" class="size-7 text-gray-300 dark:text-gray-600 <?= $logo ? 'hidden' : '' ?>"></i>
                    </div>
                    <div class="flex-1">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                            <i data-lucide="upload" class="size-4"></i> Choose logo…
                            <input type="file" name="biz_logo" accept=".png,.jpg,.jpeg,.svg,.webp" class="sr-only"
                                   onchange="(function(inp){var f=inp.files[0]; inp.parentElement.parentElement.querySelector('.js-name').textContent=f?f.name:'No file chosen'; if(f){var img=document.getElementById('logoPreview'),ph=document.getElementById('logoPlaceholder'); img.src=URL.createObjectURL(f); img.classList.remove('hidden'); ph&&ph.classList.add('hidden');}})(this)">
                        </label>
                        <p class="js-name mt-1 text-xs text-gray-500 dark:text-gray-400 truncate"><?= $logo ? esc($logo) : 'No file chosen' ?></p>
                    </div>
                </div>
            </div>

            <!-- Favicon -->
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Favicon</label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">ICO / PNG — 32×32 or 64×64.</p>
                <div class="mt-3 flex items-center gap-4">
                    <div class="flex size-12 items-center justify-center rounded-md bg-gray-50 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10 overflow-hidden">
                        <img id="favPreview" src="<?= $favicon ? base_url('uploads/' . $favicon) : '' ?>" alt="Favicon" class="max-h-full max-w-full object-contain <?= $favicon ? '' : 'hidden' ?>">
                        <i id="favPlaceholder" data-lucide="image" class="size-5 text-gray-300 dark:text-gray-600 <?= $favicon ? 'hidden' : '' ?>"></i>
                    </div>
                    <div class="flex-1">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                            <i data-lucide="upload" class="size-4"></i> Choose favicon…
                            <input type="file" name="biz_favicon" accept=".ico,.png" class="sr-only"
                                   onchange="(function(inp){var f=inp.files[0]; inp.parentElement.parentElement.querySelector('.js-name').textContent=f?f.name:'No file chosen'; if(f){var img=document.getElementById('favPreview'),ph=document.getElementById('favPlaceholder'); img.src=URL.createObjectURL(f); img.classList.remove('hidden'); ph&&ph.classList.add('hidden');}})(this)">
                        </label>
                        <p class="js-name mt-1 text-xs text-gray-500 dark:text-gray-400 truncate"><?= $favicon ? esc($favicon) : 'No file chosen' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Contact & legal ── -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4 mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Contact & legal</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Shown on invoices, receipts and public pages.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <?= view('components/form/textarea',[
                    'name'=>'biz_address','label'=>'Business address','rows'=>2,
                    'value'=>old('biz_address', $s->get('biz_address',''))
                ]) ?>
            </div>
            <?= view('components/form/input',[
                'name'=>'biz_phone','label'=>'Business phone','icon'=>'phone',
                'value'=>old('biz_phone', $s->get('biz_phone',''))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'biz_email','label'=>'Business email','type'=>'email','icon'=>'mail',
                'value'=>old('biz_email', $s->get('biz_email',''))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'biz_reg_no','label'=>'Registration #',
                'value'=>old('biz_reg_no', $s->get('biz_reg_no',''))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'biz_tax_id','label'=>'Tax / VAT ID',
                'value'=>old('biz_tax_id', $s->get('biz_tax_id',''))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'biz_hours','label'=>'Working hours','placeholder'=>'Mon-Sat 09:00-19:00',
                'value'=>old('biz_hours', $s->get('biz_hours',''))
            ]) ?>
            <div></div>
            <?= view('components/form/input',[
                'name'=>'biz_facebook','label'=>'Facebook URL','icon'=>'facebook',
                'value'=>old('biz_facebook', $s->get('biz_facebook',''))
            ]) ?>
            <?= view('components/form/input',[
                'name'=>'biz_instagram','label'=>'Instagram URL','icon'=>'instagram',
                'value'=>old('biz_instagram', $s->get('biz_instagram',''))
            ]) ?>
        </div>
    </div>

    <!-- WhatsApp floating widget -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-4">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="message-circle" class="size-4 text-green-500"></i> WhatsApp chat widget
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">A floating button on every public page that opens a WhatsApp chat with your salon. Uses the official <code class="text-xs">wa.me</code> link — no API key, works on mobile and desktop.</p>
        </div>

        <label class="flex items-start gap-3">
            <input type="checkbox" name="whatsapp_enabled" value="1" <?= $s->get('whatsapp_enabled') === '1' ? 'checked' : '' ?> class="mt-1 rounded border-gray-300 dark:border-white/10 text-green-500 focus:ring-green-500">
            <span>
                <span class="block text-sm font-semibold text-gray-900 dark:text-white">Show WhatsApp button on the website</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400">Floating green icon, bottom corner. Tap it to launch WhatsApp.</span>
            </span>
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">WhatsApp number <span class="text-xs text-gray-500 dark:text-gray-400">(with country code, no +)</span></label>
                <input name="whatsapp_number" value="<?= esc($s->get('whatsapp_number')) ?>" placeholder="94771234567"
                       class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm font-mono focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Example: <code>94771234567</code> for a Sri Lankan number.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Button position</label>
                <?php $pos = $s->get('whatsapp_position') ?: 'bottom-right'; ?>
                <select name="whatsapp_position" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="bottom-right" <?= $pos === 'bottom-right' ? 'selected' : '' ?>>Bottom right (recommended)</option>
                    <option value="bottom-left"  <?= $pos === 'bottom-left'  ? 'selected' : '' ?>>Bottom left</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Pre-filled message</label>
            <input name="whatsapp_default_message" value="<?= esc($s->get('whatsapp_default_message')) ?>" placeholder="Hi, I'd like to book an appointment."
                   class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Will appear in the chat draft when the visitor clicks. Leave blank for an empty message.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Welcome tooltip <span class="text-xs text-gray-500 dark:text-gray-400">(shows above the button)</span></label>
            <input name="whatsapp_tooltip" value="<?= esc($s->get('whatsapp_tooltip')) ?>" placeholder="Need help? Chat with us"
                   class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to skip the tooltip.</p>
        </div>
    </div>

    <!-- Google Maps -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-4">
        <div class="border-b border-gray-200 dark:border-white/10 pb-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="map" class="size-4 text-brand-500"></i> Location map
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Shown on your public Contact page. Paste a Google Maps embed iframe, or leave blank to auto-generate one from your business address above (no API key required).</p>
        </div>

        <label class="flex items-start gap-3">
            <input type="checkbox" name="biz_map_enabled" value="1" <?= $s->get('biz_map_enabled', '1') === '1' ? 'checked' : '' ?> class="mt-1 rounded border-gray-300 dark:border-white/10 text-brand-500 focus:ring-brand-500">
            <span>
                <span class="block text-sm font-semibold text-gray-900 dark:text-white">Show map on Contact page</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400">Uncheck to hide the map entirely.</span>
            </span>
        </label>

        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Google Maps embed (optional)</label>
            <textarea name="biz_map_embed" rows="4" placeholder='&lt;iframe src="https://www.google.com/maps/embed?pb=…" width="600" height="450" …&gt;&lt;/iframe&gt;'
                      class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm font-mono focus:border-brand-500 focus:ring-brand-500"><?= esc($s->get('biz_map_embed', '')) ?></textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                On <a href="https://www.google.com/maps" target="_blank" class="text-brand-600 dark:text-brand-400 underline">google.com/maps</a> search your business → <strong>Share</strong> → <strong>Embed a map</strong> → copy the HTML and paste it here.
                Leave blank and we'll auto-build a basic map from your business address.
            </p>
        </div>

        <?php $hasMap = trim((string) $s->get('biz_map_embed')) !== '' || trim((string) $s->get('biz_address')) !== ''; ?>
        <?php if ($hasMap): ?>
            <div class="rounded-md bg-blue-50 dark:bg-blue-500/10 ring-1 ring-blue-200 dark:ring-blue-500/30 px-3 py-2 text-xs text-blue-800 dark:text-blue-300 flex items-center gap-2">
                <i data-lucide="external-link" class="size-4"></i>
                <a href="<?= site_url('contact') ?>" target="_blank" class="underline">Preview Contact page</a>
                <span>— the map shows below the contact info block.</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="flex items-center justify-end gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700">
            <i data-lucide="check" class="size-4"></i> Save business settings
        </button>
    </div>
</form>
