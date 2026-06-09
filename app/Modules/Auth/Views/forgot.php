<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Reset your password</h2>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your account email and we'll send you a link to choose a new password.</p>
</div>

<form method="POST" action="<?= site_url('forgot') ?>" class="space-y-5">
    <?= csrf_field() ?>
    <div>
        <label for="email" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Email address</label>
        <div class="mt-2 relative">
            <i data-lucide="mail" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 dark:text-gray-500"></i>
            <input id="email" name="email" type="email" required autocomplete="email" placeholder="you@example.com"
                   value="<?= esc(old('email') ?? '') ?>"
                   class="block w-full rounded-md bg-white pl-10 pr-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500">
        </div>
    </div>

    <button type="submit"
            class="group flex w-full items-center justify-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm/6 font-semibold text-white shadow-sm shadow-brand-600/30 hover:bg-brand-700 dark:bg-brand-500 dark:shadow-none dark:hover:bg-brand-400">
        Send reset link
        <i data-lucide="send" class="size-4"></i>
    </button>

    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
        <a href="<?= site_url('login') ?>" class="font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">← Back to sign in</a>
    </p>
</form>
