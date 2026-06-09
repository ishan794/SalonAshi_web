<div class="space-y-4">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Add review</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Enter a review by hand — useful for testimonials gathered offline. Manually-added reviews are auto-approved.</p>
    </div>

    <form method="POST" action="<?= site_url('admin/reviews') ?>" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-4 max-w-2xl">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Reviewer name <span class="text-red-500">*</span></label>
            <input name="reviewer_name" required class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Rating <span class="text-red-500">*</span></label>
            <select name="rating" required class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>"><?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?> (<?= $i ?>)</option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Title (optional)</label>
            <input name="title" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Review body <span class="text-red-500">*</span></label>
            <textarea name="body" rows="5" required class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
            <input type="checkbox" name="is_featured" value="1" class="rounded border-gray-300 dark:border-white/10 text-brand-500 focus:ring-brand-500">
            Feature this review on the home page
        </label>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-white/10">
            <a href="<?= site_url('admin/reviews') ?>" class="rounded-md bg-gray-100 dark:bg-white/5 px-3 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-200">Cancel</a>
            <button class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700"><i data-lucide="check" class="size-4"></i> Save review</button>
        </div>
    </form>
</div>
