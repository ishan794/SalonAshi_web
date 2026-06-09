<!DOCTYPE html>
<html lang="en" class="h-full bg-white dark:bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Sign in — SalonCMS') ?></title>
    <script>
      (function(){
        const t = localStorage.getItem('saloncms_theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        }
      })();
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: { extend: { colors: { brand: {
          50:'#fdf2f8',100:'#fce7f3',200:'#fbcfe8',300:'#f9a8d4',400:'#f472b6',
          500:'#ec4899',600:'#db2777',700:'#be185d',800:'#9d174d',900:'#831843'
        } }, animation: {
          'blob': 'blob 18s infinite ease-in-out',
        }, keyframes: {
          blob: {
            '0%,100%': { transform: 'translate(0,0) scale(1)' },
            '33%':     { transform: 'translate(30px,-40px) scale(1.1)' },
            '66%':     { transform: 'translate(-20px,20px) scale(0.95)' },
          }
        } } }
      }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="h-full">
<div class="flex min-h-full flex-1">
    <!-- ────── Left: form column ────── -->
    <div class="relative flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white dark:bg-gray-900">

        <!-- Theme toggle (top-right of form col) -->
        <div class="absolute top-6 right-6"
             x-data="{ theme: localStorage.getItem('saloncms_theme') || 'system', menu: false,
                       set(t){ this.theme=t; localStorage.setItem('saloncms_theme',t);
                         const d = t==='dark' || (t==='system' && matchMedia('(prefers-color-scheme: dark)').matches);
                         document.documentElement.classList.toggle('dark', d); this.menu=false;
                         this.$nextTick(()=>window.lucide&&lucide.createIcons()); } }"
             @click.outside="menu=false">
            <button @click="menu=!menu" class="inline-flex items-center justify-center size-9 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                <i x-show="theme==='light'" data-lucide="sun" class="size-5"></i>
                <i x-show="theme==='dark'" x-cloak data-lucide="moon" class="size-5"></i>
                <i x-show="theme==='system'" x-cloak data-lucide="monitor" class="size-5"></i>
            </button>
            <div x-show="menu" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 mt-2 w-36 rounded-md bg-white py-1 shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                <button @click="set('light')" class="flex w-full items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"><i data-lucide="sun" class="size-4"></i> Light</button>
                <button @click="set('dark')" class="flex w-full items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"><i data-lucide="moon" class="size-4"></i> Dark</button>
                <button @click="set('system')" class="flex w-full items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"><i data-lucide="monitor" class="size-4"></i> System</button>
            </div>
        </div>

        <div class="mx-auto w-full max-w-sm lg:w-96">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="flex size-11 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/30">
                        <i data-lucide="scissors" class="size-6"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">SalonCMS</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Booking & Billing Suite</p>
                    </div>
                </div>
                <h2 class="mt-10 text-2xl/9 font-bold tracking-tight text-gray-900 dark:text-white">Sign in to your dashboard</h2>
                <p class="mt-2 text-sm/6 text-gray-500 dark:text-gray-400">
                    Manage appointments, customers, billing — all in one place.
                </p>
            </div>

            <div class="mt-10">
                <?php if (session('flash_success')): ?>
                    <div class="mb-4 rounded-md bg-green-50 p-3 ring-1 ring-green-200 dark:bg-green-500/10 dark:ring-green-500/30">
                        <div class="flex items-start gap-2">
                            <i data-lucide="check-circle-2" class="size-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5"></i>
                            <p class="text-sm text-green-800 dark:text-green-300"><?= esc(session('flash_success')) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (session('flash_error')): ?>
                    <div class="mb-4 rounded-md bg-red-50 p-3 ring-1 ring-red-200 dark:bg-red-500/10 dark:ring-red-500/30">
                        <div class="flex items-start gap-2">
                            <i data-lucide="alert-circle" class="size-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5"></i>
                            <p class="text-sm text-red-800 dark:text-red-300"><?= esc(session('flash_error')) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>

            <p class="mt-10 text-center text-xs text-gray-500 dark:text-gray-500">
                © <?= date('Y') ?> SalonCMS · v1.0
            </p>
        </div>
    </div>

    <!-- ────── Right: brand panel ────── -->
    <div class="relative hidden w-0 flex-1 lg:block">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-600 via-pink-500 to-purple-600 dark:from-brand-700 dark:via-pink-700 dark:to-purple-800"></div>

        <!-- Animated blobs -->
        <div aria-hidden="true" class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -left-20 size-72 rounded-full bg-white/20 blur-3xl animate-blob"></div>
            <div class="absolute top-1/3 right-0 size-80 rounded-full bg-purple-300/30 blur-3xl animate-blob" style="animation-delay: -6s"></div>
            <div class="absolute bottom-0 left-1/3 size-96 rounded-full bg-pink-300/20 blur-3xl animate-blob" style="animation-delay: -12s"></div>
        </div>

        <!-- Grid pattern overlay -->
        <svg aria-hidden="true" class="absolute inset-0 size-full text-white/10">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>

        <!-- Content -->
        <div class="relative flex h-full flex-col justify-between p-12 xl:p-16 text-white">
            <div class="flex items-center gap-2">
                <div class="flex size-9 items-center justify-center rounded-lg bg-white/15 backdrop-blur-sm ring-1 ring-white/20">
                    <i data-lucide="sparkles" class="size-5"></i>
                </div>
                <span class="text-sm font-semibold tracking-wide uppercase">SalonCMS</span>
            </div>

            <div class="max-w-md space-y-8">
                <div>
                    <h1 class="text-3xl xl:text-4xl/tight font-bold tracking-tight">
                        Run your salon<br>like a pro.
                    </h1>
                    <p class="mt-4 text-base/7 text-white/85">
                        Book appointments in seconds, track every payment, and grow loyal customers — all from one beautiful dashboard.
                    </p>
                </div>

                <ul class="space-y-3.5 text-sm">
                    <li class="flex items-start gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20 backdrop-blur-sm">
                            <i data-lucide="calendar-check-2" class="size-4"></i>
                        </span>
                        <span class="pt-0.5"><strong class="font-semibold">Smart bookings</strong> — multi-service, conflict-free, drag & drop calendar.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20 backdrop-blur-sm">
                            <i data-lucide="receipt" class="size-4"></i>
                        </span>
                        <span class="pt-0.5"><strong class="font-semibold">One-tap invoices</strong> — generate from appointments, split payments, print receipts.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20 backdrop-blur-sm">
                            <i data-lucide="users-round" class="size-4"></i>
                        </span>
                        <span class="pt-0.5"><strong class="font-semibold">Customer love</strong> — visit history, loyalty tiers, birthday reminders.</span>
                    </li>
                </ul>

                <div class="rounded-2xl bg-white/10 ring-1 ring-white/20 p-5 backdrop-blur-sm">
                    <div class="flex items-center gap-1 text-amber-300">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <svg viewBox="0 0 20 20" fill="currentColor" class="size-4"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                    <p class="mt-2.5 text-sm/6 italic text-white/90">
                        "Cut my booking errors to zero and gave my front desk 2 hours back every day."
                    </p>
                    <p class="mt-2 text-xs font-semibold tracking-wide uppercase text-white/70">— Sanduni, Glow Beauty Lounge</p>
                </div>
            </div>

            <p class="text-xs text-white/60">© <?= date('Y') ?> SalonCMS · Crafted in Colombo 🇱🇰</p>
        </div>
    </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());
  document.addEventListener('alpine:initialized', () => setTimeout(() => window.lucide && lucide.createIcons(), 50));
</script>
</body>
</html>
