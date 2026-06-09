<?php
/** @var string $active, $subview ; @var \App\Modules\Settings\Models\SettingModel $s */
$tabs = [
    'general'      => ['label' => 'General',          'icon' => 'sliders-horizontal', 'desc' => 'Name, currency, taxes, prefixes'],
    'appointments' => ['label' => 'Appointments',     'icon' => 'calendar-clock',     'desc' => 'Default duration, time slots, booking rules'],
    'business'    => ['label' => 'Business & Logo', 'icon' => 'building-2',         'desc' => 'Contact info, branding'],
    'frontend'    => ['label' => 'Public site',     'icon' => 'panels-top-left',    'desc' => 'Layout style + width'],
    'pages'       => ['label' => 'Pages',            'icon' => 'file-text',          'desc' => 'About, terms, privacy, refund'],
    'seo'         => ['label' => 'SEO',               'icon' => 'search',             'desc' => 'Per-page meta tags + OG image'],
    'integrations'=> ['label' => 'Integrations',      'icon' => 'plug',               'desc' => 'Google Business, reviews API'],
    'gateways'    => ['label' => 'Payment Gateways',  'icon' => 'credit-card',        'desc' => 'PayHere, OnePay, WebXPay credentials'],
    'loyalty'     => ['label' => 'Loyalty',         'icon' => 'award',              'desc' => 'Earn, redeem & tier rules'],
    'smtp'        => ['label' => 'SMTP / Email',    'icon' => 'mail',               'desc' => 'Outbound email server'],
    'cron'        => ['label' => 'Cron',            'icon' => 'clock',              'desc' => 'Scheduled background tasks'],
    'updates'     => ['label' => 'System Updates',  'icon' => 'refresh-cw',         'desc' => 'Check & install app updates'],
    'users'       => ['label' => 'Users',           'icon' => 'user-round-cog',     'desc' => 'Staff login accounts'],
    'roles'       => ['label' => 'Roles',           'icon' => 'shield-check',       'desc' => 'Role definitions'],
    'permissions' => ['label' => 'Permissions',     'icon' => 'key-round',          'desc' => 'Per-role access matrix'],
];
?>
<div class="space-y-5">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Settings</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Configure how SalonCMS runs for your salon.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar tabs -->
        <aside class="lg:col-span-1">
            <nav class="space-y-1">
                <?php foreach ($tabs as $key => $t):
                    $isActive = $active === $key;
                ?>
                    <a href="<?= site_url('admin/settings/' . $key) ?>"
                       class="group flex items-start gap-3 rounded-md px-3 py-2.5 transition-colors <?= $isActive
                           ? 'bg-brand-50 dark:bg-brand-500/10'
                           : 'hover:bg-gray-50 dark:hover:bg-white/5' ?>">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-md <?= $isActive
                            ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30'
                            : 'bg-gray-100 text-gray-500 group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-white/5 dark:text-gray-400 dark:group-hover:bg-brand-500/15 dark:group-hover:text-brand-300' ?>">
                            <i data-lucide="<?= esc($t['icon']) ?>" class="size-4"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold <?= $isActive ? 'text-brand-700 dark:text-brand-300' : 'text-gray-900 dark:text-white' ?>">
                                <?= esc($t['label']) ?>
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate"><?= esc($t['desc']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <!-- Active tab content -->
        <section class="lg:col-span-3">
            <?= view($subview, ['s' => $s]) ?>
        </section>
    </div>
</div>
