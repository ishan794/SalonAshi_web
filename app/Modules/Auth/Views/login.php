<form method="POST" action="<?= site_url('login') ?>" class="space-y-5">
    <?= csrf_field() ?>

    <div>
        <label for="email" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Email address</label>
        <div class="mt-2 relative">
            <i data-lucide="mail" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 dark:text-gray-500"></i>
            <input id="email" name="email" type="email" required autocomplete="email" placeholder="admin@saloncms.local"
                   value="<?= esc(old('email') ?? '') ?>"
                   class="block w-full rounded-md bg-white pl-10 pr-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Password</label>
            <a href="<?= site_url('forgot') ?>" class="text-xs font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300">Forgot?</a>
        </div>
        <div class="mt-2 relative" x-data="{ show: false }">
            <i data-lucide="lock" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 dark:text-gray-500"></i>
            <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password" placeholder="••••••••"
                   class="block w-full rounded-md bg-white pl-10 pr-10 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-brand-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-brand-500">
            <button type="button" @click="show = !show" class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex size-7 items-center justify-center rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i x-show="!show" data-lucide="eye" class="size-4"></i>
                <i x-show="show" x-cloak data-lucide="eye-off" class="size-4"></i>
            </button>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <input id="remember" name="remember" type="checkbox" class="size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:checked:bg-brand-500 dark:checked:border-brand-500">
            <label for="remember" class="text-sm text-gray-700 dark:text-gray-300">Remember me</label>
        </div>
    </div>

    <button type="submit"
            class="group flex w-full items-center justify-center gap-2 rounded-md bg-brand-600 px-3.5 py-2 text-sm/6 font-semibold text-white shadow-sm shadow-brand-600/30 hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 dark:bg-brand-500 dark:shadow-none dark:hover:bg-brand-400">
        Sign in
        <i data-lucide="arrow-right" class="size-4 transition group-hover:translate-x-0.5"></i>
    </button>

</form>
