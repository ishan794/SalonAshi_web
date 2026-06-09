<?php /** @var \App\Modules\Settings\Models\SettingModel $s */
$placeId  = $s->get('google_place_id');
$apiKey   = $s->get('google_places_api_key');
$rating   = $s->get('google_rating');
$count    = $s->get('google_review_count');
$lastRun  = $s->get('google_last_import_at');
$autoApprove = $s->get('reviews_auto_approve') === '1';
?>
<div class="space-y-5">
    <!-- Google Business -->
    <form method="POST" action="<?= site_url('admin/settings/integrations') ?>" class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-4">
        <?= csrf_field() ?>
        <div class="border-b border-gray-200 dark:border-white/10 pb-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="badge-check" class="size-4 text-blue-500"></i> Google Business reviews
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pull your Google Business reviews into the website. Both fields are required.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Google Place ID</label>
            <input name="google_place_id" value="<?= esc($placeId) ?>" placeholder="ChIJN1t_tDeuEmsRUsoyG83frY4" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm font-mono focus:border-brand-500 focus:ring-brand-500">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Find your Place ID at <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" class="text-brand-600 dark:text-brand-400 underline">developers.google.com/maps/documentation/places/web-service/place-id</a>.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Google Places API key</label>
            <input name="google_places_api_key" value="<?= esc($apiKey) ?>" placeholder="AIza…" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm font-mono focus:border-brand-500 focus:ring-brand-500">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Create one in <a href="https://console.cloud.google.com/google/maps-apis/credentials" target="_blank" class="text-brand-600 dark:text-brand-400 underline">Google Cloud Console</a>. Enable the <em>Places API</em> for the project, then restrict the key to your domain.</p>
        </div>

        <?php if ($rating || $count || $lastRun): ?>
            <div class="rounded-md bg-blue-50 dark:bg-blue-500/10 p-3 ring-1 ring-blue-200 dark:ring-blue-500/30 text-sm text-blue-800 dark:text-blue-300 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <?php if ($rating): ?><div><span class="text-xs uppercase tracking-wide">Average</span><div class="font-semibold"><?= number_format((float)$rating, 1) ?> / 5</div></div><?php endif; ?>
                <?php if ($count):  ?><div><span class="text-xs uppercase tracking-wide">Total reviews</span><div class="font-semibold"><?= (int)$count ?></div></div><?php endif; ?>
                <?php if ($lastRun): ?><div><span class="text-xs uppercase tracking-wide">Last import</span><div class="font-semibold"><?= esc($lastRun) ?></div></div><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-white/10">
            <button formaction="<?= site_url('admin/reviews/import-google') ?>" formmethod="POST" type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-white dark:bg-gray-900 ring-1 ring-gray-300 dark:ring-white/10 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5">
                <i data-lucide="download" class="size-4"></i> Import reviews now
            </button>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="check" class="size-4"></i> Save integration
            </button>
        </div>
    </form>

    <!-- In-app reviews moderation -->
    <form method="POST" action="<?= site_url('admin/settings/integrations') ?>" class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-4">
        <?= csrf_field() ?>
        <div class="border-b border-gray-200 dark:border-white/10 pb-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="message-square" class="size-4 text-brand-500"></i> In-app review moderation
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Control how reviews submitted by customers via your booking confirmation page are handled.</p>
        </div>

        <label class="flex items-start gap-3">
            <input type="checkbox" name="reviews_auto_approve" value="1" <?= $autoApprove ? 'checked' : '' ?> class="mt-1 rounded border-gray-300 dark:border-white/10 text-brand-500 focus:ring-brand-500">
            <span>
                <span class="block text-sm font-semibold text-gray-900 dark:text-white">Auto-approve customer reviews</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400">When OFF, every customer review needs a manual approval in the Reviews admin page. Recommended to keep ON only after you've used the site for a while.</span>
            </span>
        </label>

        <div class="flex justify-end pt-3 border-t border-gray-100 dark:border-white/10">
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="check" class="size-4"></i> Save moderation
            </button>
        </div>
    </form>
</div>
