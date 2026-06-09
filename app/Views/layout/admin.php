<?php
$user = session('user') ?: ['name' => 'Guest', 'email' => '', 'role' => ''];
$current = service('uri')->getPath();

$isActive = static function (string $href) use ($current): bool {
    $p = ltrim($href, '/');
    return $current === $p || str_starts_with($current, $p . '/');
};
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-white dark:bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'SalonCMS') ?></title>
    <script>
      // Set theme BEFORE Tailwind loads to avoid flash
      (function(){
        const t = localStorage.getItem('saloncms_theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        }
      })();
    </script>

    <!-- TailwindUI Plus-style typography: Inter for crisp UI text -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: { extend: {
          colors: { brand: {
            50:'#fdf2f8',100:'#fce7f3',200:'#fbcfe8',300:'#f9a8d4',400:'#f472b6',
            500:'#ec4899',600:'#db2777',700:'#be185d',800:'#9d174d',900:'#831843'
          } },
          fontFamily: {
            sans: ['Inter','ui-sans-serif','system-ui','-apple-system','Segoe UI','Roboto','sans-serif'],
          }
        } }
      }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      [x-cloak]{display:none!important}
      /* Force solid bg on shell elements when dark mode is active (Tailwind CDN dark: on html/body can fail). */
      html.dark, html.dark body { background-color: #0b1220 !important; }
      /* Pretty scrollbar in sidebar */
      .sb-scroll::-webkit-scrollbar { width: 6px; }
      .sb-scroll::-webkit-scrollbar-track { background: transparent; }
      .sb-scroll::-webkit-scrollbar-thumb { background: rgba(156,163,175,.3); border-radius: 3px; }
      .sb-scroll::-webkit-scrollbar-thumb:hover { background: rgba(156,163,175,.5); }
      /* Dark-mode native <select> dropdown options — Tailwind can't style the option popup,
         so force readable colours here. Fixes white-on-white dropdown lists across all forms. */
      html.dark select { color: #f3f4f6; }
      html.dark select option,
      html.dark select optgroup {
        background-color: #1f2937 !important;  /* gray-800 */
        color: #f3f4f6 !important;             /* gray-100 */
      }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased pb-16 lg:pb-0">
<div x-data="{ sidebarOpen: false, userMenu: false, theme: localStorage.getItem('saloncms_theme') || 'system',
               applyTheme(t){ this.theme = t; localStorage.setItem('saloncms_theme', t);
                 const dark = t === 'dark' || (t === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
                 document.documentElement.classList.toggle('dark', dark);
                 this.$nextTick(()=>window.lucide&&lucide.createIcons()); } }" class="min-h-full">

    <!-- Mobile sidebar (slide-in drawer with frosted backdrop) -->
    <div x-show="sidebarOpen" x-cloak class="relative z-50 lg:hidden"
         x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm" @click="sidebarOpen = false"></div>
        <div class="fixed inset-0 flex">
            <div x-show="sidebarOpen" x-cloak
                 x-transition:enter="transition transform ease-out duration-300"
                 x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition transform ease-in duration-200"
                 x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                 class="relative flex w-full max-w-[18rem] flex-1">
                <button type="button" @click="sidebarOpen = false"
                        class="absolute -right-12 top-4 flex size-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 ring-1 ring-white/20 backdrop-blur">
                    <i data-lucide="x" class="size-5"></i>
                </button>
                <?php include __DIR__ . '/_admin_sidebar.php'; ?>
            </div>
        </div>
    </div>

    <!-- Desktop sidebar -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <?php include __DIR__ . '/_admin_sidebar.php'; ?>
    </div>

    <!-- Main column (explicit bg so empty space to the right of narrow forms stays dark) -->
    <div class="lg:pl-72 bg-gray-50 dark:bg-gray-950 min-h-screen">
        <!-- Topbar -->
        <div class="sticky top-0 z-40 flex h-14 sm:h-16 shrink-0 items-center gap-x-3 border-b border-gray-200 bg-white/95 backdrop-blur px-3 sm:px-6 lg:px-8 dark:border-white/10 dark:bg-gray-900/90">
            <button type="button" @click="sidebarOpen = true" aria-label="Open menu"
                    class="flex size-9 items-center justify-center rounded-md text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5 lg:hidden">
                <i data-lucide="menu" class="size-5"></i>
            </button>

            <div class="flex flex-1 items-center gap-x-3 self-stretch justify-between">
                <!-- Page title -->
                <div class="min-w-0 flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white truncate"><?= esc($title ?? '') ?></h1>
                </div>

                <div class="flex items-center gap-x-3">
                    <!-- Notifications bell -->
                    <div class="relative" x-data="notifBell()" @click.outside="open = false">
                        <button type="button" @click="toggle()" class="relative -m-1.5 flex size-9 items-center justify-center rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/5" aria-label="Notifications">
                            <i data-lucide="bell" class="size-5"></i>
                            <span x-show="unread > 0" x-cloak x-text="unread > 9 ? '9+' : unread"
                                  class="absolute -top-0.5 -right-0.5 flex min-w-[18px] h-[18px] items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold ring-2 ring-white dark:ring-gray-900 px-1"></span>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 z-30 mt-2 w-80 sm:w-96 origin-top-right rounded-lg bg-white shadow-xl ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10 overflow-hidden">
                            <div class="px-4 py-2.5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</p>
                                <a href="<?= site_url('admin/notifications') ?>" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">View all</a>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="loading"><div class="px-4 py-8 text-center text-sm text-gray-500"><i data-lucide="loader-2" class="size-4 animate-spin inline mr-1"></i> Loading…</div></template>
                                <template x-if="!loading && items.length === 0"><div class="px-4 py-8 text-center text-sm text-gray-500"><i data-lucide="bell-off" class="size-6 mx-auto text-gray-300"></i><p class="mt-2">All caught up.</p></div></template>
                                <template x-for="n in items" :key="n.id">
                                    <a :href="n.link || '<?= site_url('admin/notifications') ?>'"
                                       :class="!n.is_read && 'bg-brand-50/30 dark:bg-brand-500/5'"
                                       class="block px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5 border-b border-gray-100 dark:border-white/10 last:border-0">
                                        <div class="flex items-start gap-2.5">
                                            <span :class="`bg-${n.color || 'gray'}-100 dark:bg-${n.color || 'gray'}-500/20 text-${n.color || 'gray'}-600 dark:text-${n.color || 'gray'}-300`" class="flex size-7 items-center justify-center rounded-full shrink-0 mt-0.5">
                                                <i :data-lucide="n.icon || 'bell'" class="size-3.5"></i>
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="n.title"></p>
                                                <p x-show="n.body" class="mt-0.5 text-xs text-gray-600 dark:text-gray-400 line-clamp-2" x-text="n.body"></p>
                                                <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500" x-text="formatDate(n.created_at)"></p>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <form method="POST" action="<?= site_url('admin/notifications/mark-all-read') ?>" class="px-4 py-2 border-t border-gray-100 dark:border-white/10 text-center bg-gray-50 dark:bg-white/5">
                                <?= csrf_field() ?>
                                <button class="text-xs font-semibold text-gray-700 dark:text-gray-300 hover:text-brand-600">Mark all as read</button>
                            </form>
                        </div>
                    </div>

                    <!-- Theme toggle -->
                    <div class="relative" x-data="{ themeMenu: false }" @click.outside="themeMenu = false">
                        <button type="button" @click="themeMenu = !themeMenu; userMenu = false"
                                class="-m-1.5 flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                            <span class="sr-only">Theme</span>
                            <i x-show="theme === 'light'" data-lucide="sun" class="size-5"></i>
                            <i x-show="theme === 'dark'" x-cloak data-lucide="moon" class="size-5"></i>
                            <i x-show="theme === 'system'" x-cloak data-lucide="monitor" class="size-5"></i>
                        </button>
                        <div x-show="themeMenu" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 z-20 mt-2.5 w-36 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                            <button @click="applyTheme('light'); themeMenu=false" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">
                                <i data-lucide="sun" class="size-4"></i> Light
                            </button>
                            <button @click="applyTheme('dark'); themeMenu=false" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">
                                <i data-lucide="moon" class="size-4"></i> Dark
                            </button>
                            <button @click="applyTheme('system'); themeMenu=false" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">
                                <i data-lucide="monitor" class="size-4"></i> System
                            </button>
                        </div>
                    </div>

                    <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200 dark:lg:bg-white/10"></div>

                    <!-- User menu -->
                    <div class="relative" @click.outside="userMenu = false">
                        <button type="button" @click="userMenu = !userMenu" class="-m-1.5 flex items-center p-1.5">
                            <span class="sr-only">Open user menu</span>
                            <div class="flex size-8 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                                <?= esc(strtoupper(substr($user['name'] ?? '?', 0, 1))) ?>
                            </div>
                            <span class="hidden lg:flex lg:items-center">
                                <span class="ml-3 text-sm font-semibold leading-6 text-gray-900 dark:text-white"><?= esc($user['name'] ?? '') ?></span>
                                <i data-lucide="chevron-down" class="ml-2 size-4 text-gray-400"></i>
                            </span>
                        </button>
                        <div x-show="userMenu" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 z-20 mt-2.5 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="px-3 py-2 border-b border-gray-100 dark:border-white/10">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Signed in as</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= esc($user['email'] ?? '') ?></p>
                                <p class="mt-0.5 text-xs text-brand-600 dark:text-brand-400"><?= esc($user['role_label'] ?? '') ?></p>
                            </div>
                            <a href="<?= site_url('admin/profile') ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5">
                                <i data-lucide="user-round" class="size-4"></i> My profile
                            </a>
                            <a href="<?= site_url('admin/profile') ?>#password" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5">
                                <i data-lucide="key-round" class="size-4"></i> Change password
                            </a>
                            <div class="my-1 border-t border-gray-100 dark:border-white/10"></div>
                            <a href="<?= site_url('logout') ?>" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5">
                                <i data-lucide="log-out" class="size-4"></i> Sign out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page content -->
        <main class="py-6">
            <div class="px-4 sm:px-6 lg:px-8">
                <?php if (session('flash_success')): ?>
                    <div class="mb-4 rounded-md bg-green-50 p-3 ring-1 ring-green-200 dark:bg-green-500/10 dark:ring-green-500/30">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="size-5 text-green-600 dark:text-green-400"></i>
                            <p class="text-sm text-green-800 dark:text-green-300"><?= esc(session('flash_success')) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (session('flash_error')): ?>
                    <div class="mb-4 rounded-md bg-red-50 p-3 ring-1 ring-red-200 dark:bg-red-500/10 dark:ring-red-500/30">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="size-5 text-red-600 dark:text-red-400"></i>
                            <p class="text-sm text-red-800 dark:text-red-300"><?= esc(session('flash_error')) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <!-- Mobile bottom navigation (always visible on mobile, hidden on lg+) -->
    <?php
    $bottomNav = [
        ['admin/dashboard',    'Home',     'layout-dashboard'],
        ['admin/appointments', 'Calendar', 'calendar-days'],
        ['admin/pos',          'POS',      'scan-line'],
        ['admin/customers',    'People',   'users'],
    ];
    ?>
    <nav class="fixed inset-x-0 bottom-0 z-40 lg:hidden border-t border-gray-200 dark:border-white/10 bg-white/95 dark:bg-gray-900/95 backdrop-blur shadow-[0_-4px_12px_rgba(0,0,0,0.04)] dark:shadow-none">
        <div class="grid grid-cols-5 h-14">
            <?php foreach ($bottomNav as [$href, $label, $icon]): $active = $isActive($href); ?>
                <a href="<?= site_url($href) ?>"
                   class="flex flex-col items-center justify-center gap-0.5 transition-colors <?= $active
                       ? 'text-brand-600 dark:text-brand-400'
                       : 'text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400' ?>">
                    <i data-lucide="<?= esc($icon) ?>" class="size-5"></i>
                    <span class="text-[10px] font-semibold leading-none"><?= esc($label) ?></span>
                </a>
            <?php endforeach; ?>
            <button type="button" @click="sidebarOpen = true" class="flex flex-col items-center justify-center gap-0.5 text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400">
                <i data-lucide="menu" class="size-5"></i>
                <span class="text-[10px] font-semibold leading-none">Menu</span>
            </button>
        </div>
    </nav>
</div>
<script>
  // Render Lucide icons once on load and once after Alpine init.
  // Avoid a MutationObserver — it triggers infinite reflow loops during Alpine transitions.
  function renderIcons(){ window.lucide && lucide.createIcons(); }
  document.addEventListener('DOMContentLoaded', renderIcons);
  document.addEventListener('alpine:initialized', () => setTimeout(renderIcons, 50));
  document.addEventListener('click', () => setTimeout(renderIcons, 30));

  // ── Notification bell ── polls /admin/notifications/feed every 30s when tab is visible.
  function notifBell() {
    return {
      open: false,
      loading: false,
      unread: 0,
      items: [],
      poll: null,
      init() {
        this.refresh();
        this.poll = setInterval(() => { if (! document.hidden) this.refresh(); }, 30000);
      },
      toggle() { this.open = !this.open; if (this.open) this.refresh(); this.$nextTick(renderIcons); },
      async refresh() {
        this.loading = true;
        try {
          const r = await fetch('<?= site_url('admin/notifications/feed') ?>', { credentials: 'same-origin' });
          if (r.ok) {
            const d = await r.json();
            this.unread = d.unread || 0;
            this.items  = d.items  || [];
          }
        } catch (e) {}
        this.loading = false;
        this.$nextTick(renderIcons);
      },
      formatDate(s) {
        if (!s) return '';
        const d = new Date(s.replace(' ', 'T'));
        const diff = Math.floor((Date.now() - d.getTime()) / 1000);
        if (diff < 60)    return 'just now';
        if (diff < 3600)  return Math.floor(diff/60)   + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return d.toLocaleDateString();
      }
    };
  }

  // ── Modern date picker (used by components/form/datepicker.php and inline pickers) ──
  function datepicker(cfg) {
    return {
      value:  (cfg && cfg.value) || '',
      min:    (cfg && cfg.min)   || '',
      max:    (cfg && cfg.max)   || '',
      open:   false,
      cursor: ((cfg && cfg.value) || new Date().toISOString().slice(0,10)).slice(0,7),

      toggle() {
        this.open = !this.open;
        if (this.open && this.value) this.cursor = this.value.slice(0,7);
        this.$nextTick(renderIcons);
      },
      formatted() {
        if (!this.value) return '';
        const d = new Date(this.value + 'T00:00');
        return d.toLocaleDateString(undefined, { weekday:'short', year:'numeric', month:'short', day:'numeric' });
      },
      monthLabel() {
        const d = new Date(this.cursor + '-01T00:00');
        return d.toLocaleDateString(undefined, { year:'numeric', month:'long' });
      },
      cells() {
        const [y, m] = this.cursor.split('-').map(Number);
        const dow  = new Date(y, m - 1, 1).getDay();
        const days = new Date(y, m, 0).getDate();
        const out = [];
        for (let i = 0; i < dow; i++) out.push(null);
        for (let d = 1; d <= days; d++)
          out.push(`${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
        while (out.length < 42) out.push(null);
        return out;
      },
      isDisabled(d) {
        if (!d) return true;
        if (this.min && d < this.min) return true;
        if (this.max && d > this.max) return true;
        return false;
      },
      cellClass(d) {
        if (!d) return '';
        const today = new Date().toISOString().slice(0,10);
        if (this.value === d) return 'bg-brand-600 text-white shadow-sm shadow-brand-600/30';
        if (this.isDisabled(d)) return 'text-gray-300 dark:text-gray-600 cursor-not-allowed';
        if (d === today) return 'ring-1 ring-brand-300 dark:ring-brand-500/40 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10';
        return 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5';
      },
      pick(d) { if (!this.isDisabled(d)) { this.value = d; this.open = false; } },
      pickToday() {
        const t = new Date().toISOString().slice(0,10);
        if (! this.isDisabled(t)) this.pick(t);
        else this.cursor = t.slice(0,7);
      },
      goToToday() { this.cursor = new Date().toISOString().slice(0,7); },
      prevMonth() {
        const [y, m] = this.cursor.split('-').map(Number);
        const d = new Date(y, m - 2, 1);
        this.cursor = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
        this.$nextTick(renderIcons);
      },
      nextMonth() {
        const [y, m] = this.cursor.split('-').map(Number);
        const d = new Date(y, m, 1);
        this.cursor = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
        this.$nextTick(renderIcons);
      },
      clearValue() { this.value = ''; },
    };
  }
</script>
</body>
</html>
