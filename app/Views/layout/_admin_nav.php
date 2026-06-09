<?php
/**
 * Reusable grouped nav.
 *
 * @var array    $groups   [[label, [[href,label,icon], ...]], ...]
 * @var callable $isActive function(string $href): bool
 */
?>
<nav class="flex flex-1 flex-col">
    <ul role="list" class="flex flex-1 flex-col gap-y-4">

        <?php foreach ($groups as [$sectionLabel, $items]): ?>
            <li>
                <p class="px-3 mb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    <?= esc($sectionLabel) ?>
                </p>
                <ul role="list" class="space-y-0.5">
                    <?php foreach ($items as [$href, $label, $icon]): $active = $isActive($href); ?>
                        <li>
                            <a href="<?= site_url($href) ?>"
                               class="group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm/6 font-medium transition-colors <?= $active
                                   ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'
                                   : 'text-gray-700 hover:bg-gray-50 hover:text-brand-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' ?>">
                                <i data-lucide="<?= esc($icon) ?>" class="size-4.5 shrink-0 <?= $active
                                    ? 'text-brand-600 dark:text-brand-300'
                                    : 'text-gray-400 group-hover:text-brand-600 dark:text-gray-500 dark:group-hover:text-white' ?>"></i>
                                <span class="truncate"><?= esc($label) ?></span>
                                <?php if ($active): ?>
                                    <span class="ml-auto block size-1.5 rounded-full bg-brand-500"></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>

        <!-- Sign-out pinned to bottom -->
        <li class="mt-auto">
            <a href="<?= site_url('logout') ?>"
               class="group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm/6 font-medium text-gray-700 hover:bg-gray-50 hover:text-brand-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                <i data-lucide="log-out" class="size-4.5 shrink-0 text-gray-400 group-hover:text-brand-600 dark:text-gray-500 dark:group-hover:text-white"></i>
                Sign out
            </a>
        </li>
    </ul>
</nav>
