<?php /** @var array $user */ ?>
<div class="max-w-2xl space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">My Profile</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage your account details and password.</p>
    </div>

    <!-- Details -->
    <form method="POST" action="<?= site_url('admin/profile') ?>" class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-4">
        <?= csrf_field() ?>
        <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-white/10 pb-3">Account details</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Full name</label>
                <input name="name" value="<?= esc($user['name']) ?>" required class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <input name="email" type="email" value="<?= esc($user['email']) ?>" required class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                <input name="phone" value="<?= esc($user['phone'] ?? '') ?>" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
        </div>
        <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-white/10">
            <button class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700"><i data-lucide="check" class="size-4"></i> Save details</button>
        </div>
    </form>

    <!-- Password -->
    <form method="POST" action="<?= site_url('admin/profile/password') ?>" id="password" class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-4" x-data="{ show:false }">
        <?= csrf_field() ?>
        <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-white/10 pb-3">Change password</h3>
        <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Current password</label>
            <input name="current_password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="relative">
                <label class="block text-sm font-medium text-gray-900 dark:text-white">New password</label>
                <input name="new_password" :type="show?'text':'password'" required minlength="6" autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm pr-10 focus:border-brand-500 focus:ring-brand-500">
                <button type="button" @click="show=!show" tabindex="-1" class="absolute right-2 top-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"><i x-show="!show" data-lucide="eye" class="size-4"></i><i x-show="show" x-cloak data-lucide="eye-off" class="size-4"></i></button>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">Confirm new password</label>
                <input name="new_password_confirm" :type="show?'text':'password'" required minlength="6" autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
        </div>
        <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-white/10">
            <button class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700"><i data-lucide="key-round" class="size-4"></i> Update password</button>
        </div>
    </form>
</div>
