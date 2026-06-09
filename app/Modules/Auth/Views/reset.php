<?php /** @var string $token */ ?>
<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Choose a new password</h2>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pick a strong password you don't use elsewhere.</p>
</div>

<form method="POST" action="<?= site_url('reset') ?>" class="space-y-5" x-data="{ show: false }">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token) ?>">

    <div>
        <label for="password" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">New password</label>
        <div class="mt-2 relative">
            <i data-lucide="lock" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 dark:text-gray-500"></i>
            <input id="password" name="password" :type="show ? 'text' : 'password'" required minlength="6" autocomplete="new-password" placeholder="••••••••"
                   class="block w-full rounded-md bg-white pl-10 pr-10 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500">
            <button type="button" @click="show = !show" class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex size-7 items-center justify-center rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i x-show="!show" data-lucide="eye" class="size-4"></i>
                <i x-show="show" x-cloak data-lucide="eye-off" class="size-4"></i>
            </button>
        </div>
    </div>

    <div>
        <label for="password_confirm" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Confirm new password</label>
        <div class="mt-2 relative">
            <i data-lucide="lock" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 dark:text-gray-500"></i>
            <input id="password_confirm" name="password_confirm" :type="show ? 'text' : 'password'" required minlength="6" autocomplete="new-password" placeholder="••••••••"
                   class="block w-full rounded-md bg-white pl-10 pr-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500">
        </div>
    </div>

    <button type="submit"
            class="group flex w-full items-center justify-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm/6 font-semibold text-white shadow-sm shadow-brand-600/30 hover:bg-brand-700 dark:bg-brand-500 dark:shadow-none dark:hover:bg-brand-400">
        Set new password
        <i data-lucide="check" class="size-4"></i>
    </button>

    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
        <a href="<?= site_url('login') ?>" class="font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">← Back to sign in</a>
    </p>
</form>
