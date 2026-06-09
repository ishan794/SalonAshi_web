<?php
/** @var array $row, $appts, $invoices, $cancellations, $loyaltyTxns, $loyaltyTiers, $history, $notes, $allergies, $preferences, $files; @var array $reliability; @var bool $loyaltyEnabled; @var int $loyaltyBalance, $loyaltyLifetime */
$rel = $reliability;
$relCfg = [
    'good'  => ['label' => 'Reliable',   'bg' => 'bg-green-100 dark:bg-green-500/20', 'text' => 'text-green-700 dark:text-green-300', 'ring' => 'ring-green-200 dark:ring-green-500/30', 'icon' => 'shield-check'],
    'watch' => ['label' => 'Watch list', 'bg' => 'bg-amber-100 dark:bg-amber-500/20', 'text' => 'text-amber-700 dark:text-amber-300', 'ring' => 'ring-amber-200 dark:ring-amber-500/30', 'icon' => 'shield-alert'],
    'risk'  => ['label' => 'Unreliable', 'bg' => 'bg-red-100 dark:bg-red-500/20',     'text' => 'text-red-700 dark:text-red-300',     'ring' => 'ring-red-200 dark:ring-red-500/30',  'icon' => 'shield-x'],
][$rel['level']];

$tabs = [
    ['key' => 'overview',     'label' => 'Overview',        'icon' => 'gauge-circle'],
    ['key' => 'history',      'label' => 'Service history', 'icon' => 'history'],
    ['key' => 'appointments', 'label' => 'Appointments',    'icon' => 'calendar-days'],
    ['key' => 'invoices',     'label' => 'Invoices',        'icon' => 'receipt'],
    ['key' => 'notes',        'label' => 'Notes',           'icon' => 'sticky-note'],
    ['key' => 'allergies',    'label' => 'Allergies',       'icon' => 'triangle-alert'],
    ['key' => 'preferences',  'label' => 'Preferences',     'icon' => 'heart'],
    ['key' => 'photos',       'label' => 'Photos',          'icon' => 'images'],
    ['key' => 'files',        'label' => 'Files',           'icon' => 'paperclip'],
];

$severityCfg = [
    'mild'     => ['color' => 'amber', 'label' => 'Mild'],
    'moderate' => ['color' => 'orange','label' => 'Moderate'],
    'severe'   => ['color' => 'red',   'label' => 'Severe'],
];

// Photos extracted from history
$photoRows = array_values(array_filter($history, fn($h) => ! empty($h['before_image']) || ! empty($h['after_image'])));
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
     x-data='{
         tab: (window.location.hash || "").replace("#tab-","") || "overview",
         editingHistoryId: null,
         setTab(t) { this.tab = t; history.replaceState(null,"","#tab-"+t); }
     }'>
    <!-- ── LEFT: profile card ── -->
    <div class="lg:col-span-1 space-y-4">
        <!-- Allergy warning banner — always at top, very visible -->
        <?php if (! empty($allergies)): ?>
            <div class="rounded-lg bg-red-50 dark:bg-red-500/10 p-4 ring-2 ring-red-300 dark:ring-red-500/40">
                <div class="flex items-start gap-2">
                    <i data-lucide="triangle-alert" class="size-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5"></i>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-red-800 dark:text-red-300">Allergy warning</p>
                        <ul class="mt-1 space-y-0.5 text-xs text-red-800 dark:text-red-300">
                            <?php foreach ($allergies as $al): $sc = $severityCfg[$al['severity']] ?? ['color' => 'red', 'label' => $al['severity']]; ?>
                                <li class="flex items-center gap-1.5">
                                    <span class="inline-block size-1.5 rounded-full bg-<?= $sc['color'] ?>-500"></span>
                                    <strong><?= esc($al['allergy_name']) ?></strong>
                                    <span class="opacity-70">— <?= esc($sc['label']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-3">
                <div class="size-12 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center text-brand-700 dark:text-brand-300 font-semibold">
                    <?= esc(strtoupper(substr($row['full_name'],0,1))) ?>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white"><?= esc($row['full_name']) ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?= esc(ucfirst($row['membership'])) ?> · <?= (int)$row['loyalty_points'] ?> pts</p>
                </div>
            </div>
            <dl class="mt-5 space-y-2 text-sm">
                <div class="flex"><dt class="w-24 text-gray-500 dark:text-gray-400">Mobile</dt><dd class="text-gray-900 dark:text-white"><?= esc($row['mobile']) ?></dd></div>
                <div class="flex"><dt class="w-24 text-gray-500 dark:text-gray-400">Email</dt><dd class="text-gray-900 dark:text-white"><?= esc($row['email'] ?: '—') ?></dd></div>
                <div class="flex"><dt class="w-24 text-gray-500 dark:text-gray-400">Gender</dt><dd class="text-gray-900 dark:text-white"><?= esc($row['gender'] ?: '—') ?></dd></div>
                <div class="flex"><dt class="w-24 text-gray-500 dark:text-gray-400">Birthday</dt><dd class="text-gray-900 dark:text-white"><?= esc($row['birthday'] ?: '—') ?></dd></div>
                <div class="flex"><dt class="w-24 text-gray-500 dark:text-gray-400">Address</dt><dd class="text-gray-900 dark:text-white"><?= esc($row['address'] ?: '—') ?></dd></div>
            </dl>
            <div class="mt-5 flex gap-2">
                <a href="<?= site_url('admin/customers/'.$row['id'].'/edit') ?>" class="flex-1 text-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5">Edit</a>
                <a href="<?= site_url('admin/appointments/create?customer_id='.$row['id']) ?>" class="flex-1 text-center rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">Book</a>
            </div>
        </div>
    </div>

    <!-- ── RIGHT: tabbed content ── -->
    <div class="lg:col-span-2">
        <!-- Tab nav -->
        <div class="flex flex-wrap gap-1 mb-4 -mx-1 px-1 overflow-x-auto bg-white dark:bg-gray-800 rounded-lg ring-1 ring-gray-200 dark:ring-white/10 p-1">
            <?php foreach ($tabs as $t): ?>
                <button type="button" @click="setTab('<?= $t['key'] ?>')"
                        :class="tab === '<?= $t['key'] ?>' ? 'bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'"
                        class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold whitespace-nowrap">
                    <i data-lucide="<?= esc($t['icon']) ?>" class="size-3.5"></i> <?= esc($t['label']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- ── Overview tab ── -->
        <div x-show="tab === 'overview'" class="space-y-4">
            <div class="rounded-lg p-5 ring-1 <?= $relCfg['bg'] . ' ' . $relCfg['ring'] ?>">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-full <?= $relCfg['bg'] ?> <?= $relCfg['text'] ?> ring-1 <?= $relCfg['ring'] ?>">
                            <i data-lucide="<?= esc($relCfg['icon']) ?>" class="size-5"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold <?= $relCfg['text'] ?>"><?= esc($relCfg['label']) ?></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Reliability score: <strong class="<?= $relCfg['text'] ?>"><?= (int)$rel['score'] ?>/100</strong></p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= (int)$rel['total_appts'] ?> bookings</span>
                </div>
                <dl class="mt-4 grid grid-cols-4 gap-2 text-center">
                    <div class="rounded-md bg-white dark:bg-white/5 p-2">
                        <dt class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">Completed</dt>
                        <dd class="text-base font-semibold text-green-600 dark:text-green-400"><?= (int)$rel['completed'] ?></dd>
                    </div>
                    <div class="rounded-md bg-white dark:bg-white/5 p-2">
                        <dt class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">w/ notice</dt>
                        <dd class="text-base font-semibold text-gray-600 dark:text-gray-300"><?= (int)$rel['cancel_with_notice'] ?></dd>
                    </div>
                    <div class="rounded-md bg-white dark:bg-white/5 p-2">
                        <dt class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">Late cancel</dt>
                        <dd class="text-base font-semibold text-amber-600 dark:text-amber-400"><?= (int)$rel['cancel_late'] ?></dd>
                    </div>
                    <div class="rounded-md bg-white dark:bg-white/5 p-2">
                        <dt class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">No-shows</dt>
                        <dd class="text-base font-semibold text-red-600 dark:text-red-400"><?= (int)$rel['no_shows'] ?></dd>
                    </div>
                </dl>
            </div>

            <?php if ($loyaltyEnabled):
                $tier = strtolower($row['membership'] ?: 'none');
                $tierCfg = ['none'=>['label'=>'No tier','color'=>'gray'],'silver'=>['label'=>'Silver','color'=>'slate'],'gold'=>['label'=>'Gold','color'=>'amber'],'platinum'=>['label'=>'Platinum','color'=>'purple']][$tier] ?? ['label'=>'No tier','color'=>'gray'];
                $nextTier = null; $nextThresh = 0;
                foreach ($loyaltyTiers as $tName => $thresh) if ($loyaltyLifetime < $thresh) { $nextTier = $tName; $nextThresh = $thresh; break; }
                $progress = $nextThresh > 0 ? min(100, round(($loyaltyLifetime / $nextThresh) * 100)) : 100;
            ?>
                <div class="rounded-lg bg-gradient-to-br from-amber-50 to-brand-50 dark:from-amber-500/10 dark:to-brand-500/10 p-6 ring-1 ring-amber-200 dark:ring-amber-500/30">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-brand-500 text-white shadow-md shadow-amber-600/30"><i data-lucide="award" class="size-6"></i></span>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= $loyaltyBalance ?> <span class="text-sm font-normal text-gray-500 dark:text-gray-400">points</span></p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Lifetime: <?= $loyaltyLifetime ?></p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-<?= $tierCfg['color'] ?>-100 dark:bg-<?= $tierCfg['color'] ?>-500/20 px-3 py-1 text-xs font-semibold text-<?= $tierCfg['color'] ?>-700 dark:text-<?= $tierCfg['color'] ?>-300"><i data-lucide="star" class="size-3.5"></i> <?= esc($tierCfg['label']) ?></span>
                    </div>
                    <?php if ($nextTier): ?>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                <span><?= $loyaltyLifetime ?> / <?= $nextThresh ?> pts to <strong class="text-gray-900 dark:text-white capitalize"><?= esc($nextTier) ?></strong></span>
                                <span><?= $progress ?>%</span>
                            </div>
                            <div class="h-2 rounded-full bg-white/60 dark:bg-white/10 overflow-hidden"><div class="h-full bg-gradient-to-r from-amber-400 to-brand-500" style="width:<?= $progress ?>%"></div></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Service history tab (timeline) ── -->
        <div x-show="tab === 'history'" x-cloak id="tab-history" class="space-y-4">
            <?php if (empty($history)): ?>
                <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    No service history yet. Records are auto-created when an appointment is marked <strong>completed</strong>.
                </div>
            <?php else: ?>
                <ol class="relative border-l border-gray-200 dark:border-white/10 ml-3 space-y-5">
                    <?php foreach ($history as $h): ?>
                        <li class="ml-5 relative">
                            <span class="absolute -left-[26px] top-1 flex size-4 items-center justify-center rounded-full bg-brand-500 ring-4 ring-white dark:ring-gray-900"></span>
                            <div class="rounded-lg bg-white dark:bg-gray-800 p-5 ring-1 ring-gray-200 dark:ring-white/10">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white"><?= esc($h['service_name']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc(date('M j, Y H:i', strtotime($h['service_date']))) ?> · <?= esc($h['staff_name'] ?: '—') ?> · <?= (int)$h['duration_min'] ?> min · LKR <?= number_format((float)$h['price'], 0) ?></p>
                                    </div>
                                    <button type="button" @click="editingHistoryId = (editingHistoryId === <?= (int)$h['id'] ?> ? null : <?= (int)$h['id'] ?>)" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                                        <span x-text="editingHistoryId === <?= (int)$h['id'] ?> ? 'Close' : 'Edit'"></span>
                                    </button>
                                </div>

                                <?php if (! empty($h['notes']) || ! empty($h['product_used']) || ! empty($h['formula']) || $h['rating']): ?>
                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                        <?php if (! empty($h['notes'])): ?><div class="rounded-md bg-gray-50 dark:bg-white/5 p-2"><span class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px]">Notes</span><p class="mt-0.5 text-gray-800 dark:text-gray-200 whitespace-pre-line"><?= esc($h['notes']) ?></p></div><?php endif; ?>
                                        <?php if (! empty($h['product_used'])): ?><div class="rounded-md bg-gray-50 dark:bg-white/5 p-2"><span class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px]">Products used</span><p class="mt-0.5 text-gray-800 dark:text-gray-200 whitespace-pre-line"><?= esc($h['product_used']) ?></p></div><?php endif; ?>
                                        <?php if (! empty($h['formula'])): ?><div class="rounded-md bg-brand-50 dark:bg-brand-500/10 p-2 sm:col-span-2"><span class="text-brand-700 dark:text-brand-300 uppercase tracking-wide text-[10px] font-semibold">Hair / color formula</span><pre class="mt-0.5 text-brand-900 dark:text-brand-100 whitespace-pre-line font-mono text-xs"><?= esc($h['formula']) ?></pre></div><?php endif; ?>
                                        <?php if ($h['rating']): ?><div class="rounded-md bg-amber-50 dark:bg-amber-500/10 p-2"><span class="text-amber-700 dark:text-amber-300 uppercase tracking-wide text-[10px]">Rating</span><div class="mt-0.5 flex gap-0.5"><?php for ($i=1;$i<=5;$i++): ?><i data-lucide="star" class="size-3.5 <?= $i<=(int)$h['rating']?'text-amber-400 fill-amber-400':'text-gray-300' ?>"></i><?php endfor; ?></div></div><?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (! empty($h['before_image']) || ! empty($h['after_image'])): ?>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <?php foreach (['before_image' => 'Before', 'after_image' => 'After'] as $field => $label): if (empty($h[$field])) continue; ?>
                                            <a href="<?= base_url('uploads/' . $h[$field]) ?>" target="_blank" class="relative block rounded-md overflow-hidden ring-1 ring-gray-200 dark:ring-white/10 group">
                                                <img src="<?= base_url('uploads/' . $h[$field]) ?>" alt="<?= $label ?>" class="w-full h-32 object-cover group-hover:scale-105 transition">
                                                <span class="absolute top-1 left-1 bg-black/60 text-white text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded"><?= $label ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Edit form -->
                                <form x-show="editingHistoryId === <?= (int)$h['id'] ?>" x-cloak method="POST" enctype="multipart/form-data" action="<?= site_url('admin/customers/' . (int)$row['id'] . '/history/' . (int)$h['id']) ?>" class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10 space-y-3">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="PUT">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Stylist notes</label>
                                            <textarea name="notes" rows="3" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm"><?= esc($h['notes']) ?></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Products used</label>
                                            <textarea name="product_used" rows="3" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm"><?= esc($h['product_used']) ?></textarea>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Color / formula (free-form, monospace)</label>
                                        <textarea name="formula" rows="4" placeholder="Brand:&#10;Base:&#10;Developer:&#10;Mix ratio:&#10;Processing time:" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm font-mono"><?= esc($h['formula']) ?></textarea>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Before photo</label>
                                            <input type="file" name="before_image" accept="image/*" class="mt-1 block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-brand-50 dark:file:bg-brand-500/15 file:text-brand-700 dark:file:text-brand-300 file:font-semibold">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">After photo</label>
                                            <input type="file" name="after_image" accept="image/*" class="mt-1 block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-brand-50 dark:file:bg-brand-500/15 file:text-brand-700 dark:file:text-brand-300 file:font-semibold">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Customer rating</label>
                                            <select name="rating" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                                                <option value="">—</option>
                                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                                    <option value="<?= $i ?>" <?= (int)$h['rating'] === $i ? 'selected' : '' ?>><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button class="inline-flex items-center gap-1 rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"><i data-lucide="check" class="size-3.5"></i> Save</button>
                                    </div>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>

        <!-- ── Appointments tab ── -->
        <div x-show="tab === 'appointments'" x-cloak class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <?php if (empty($appts)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400">No appointments yet.</p>
            <?php else: ?>
                <ul class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    <?php foreach ($appts as $a): ?>
                        <li class="py-2 flex items-center justify-between">
                            <a href="<?= site_url('admin/appointments/' . (int)$a['id']) ?>" class="hover:text-brand-600 dark:hover:text-brand-400"><?= esc($a['code']) ?> · <?= esc($a['start_at']) ?></a>
                            <span class="inline-flex rounded-full bg-gray-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300"><?= esc($a['status']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- ── Invoices tab ── -->
        <div x-show="tab === 'invoices'" x-cloak class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <?php if (empty($invoices)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400">No invoices yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead><tr class="text-left text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide"><th class="py-2">Invoice</th><th>Total</th><th>Paid</th><th>Status</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php foreach ($invoices as $i): ?>
                                <tr>
                                    <td class="py-2"><a href="<?= site_url('admin/billing/invoices/'.$i['id']) ?>" class="text-brand-600 dark:text-brand-400 hover:text-brand-700"><?= esc($i['invoice_no'] ?? $i['code'] ?? ('#' . $i['id'])) ?></a></td>
                                    <td>LKR <?= number_format((float)$i['total'], 2) ?></td>
                                    <td>LKR <?= number_format((float)($i['paid'] ?? 0), 2) ?></td>
                                    <td><span class="inline-flex rounded-full bg-gray-100 dark:bg-white/10 px-2 py-0.5 text-xs"><?= esc($i['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Notes tab ── -->
        <div x-show="tab === 'notes'" x-cloak class="space-y-4">
            <form method="POST" action="<?= site_url('admin/customers/' . (int)$row['id'] . '/notes') ?>" class="rounded-lg bg-white dark:bg-gray-800 p-5 ring-1 ring-gray-200 dark:ring-white/10 space-y-3">
                <?= csrf_field() ?>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Title (optional)</label>
                        <input name="title" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Type</label>
                        <select name="note_type" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                            <option value="general">General</option>
                            <option value="hair">Hair condition</option>
                            <option value="skin">Skin condition</option>
                            <option value="recommendation">Recommendation</option>
                            <option value="formula">Color formula</option>
                            <option value="warning">Warning</option>
                        </select>
                    </div>
                </div>
                <textarea name="body" rows="3" required placeholder="What should the next stylist know?" class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm"></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200"><input type="checkbox" name="is_pinned" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500"> Pin to top</label>
                    <button class="inline-flex items-center gap-1 rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"><i data-lucide="plus" class="size-3.5"></i> Add note</button>
                </div>
            </form>

            <?php if (empty($notes)): ?>
                <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-6 text-center text-sm text-gray-500 dark:text-gray-400">No notes yet.</div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($notes as $n): ?>
                        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 ring-1 <?= $n['is_pinned'] ? 'ring-brand-300 dark:ring-brand-500/40' : 'ring-gray-200 dark:ring-white/10' ?>">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <?php if ($n['is_pinned']): ?><i data-lucide="pin" class="size-3.5 text-brand-500"></i><?php endif; ?>
                                        <?php if (! empty($n['title'])): ?><p class="font-semibold text-gray-900 dark:text-white text-sm"><?= esc($n['title']) ?></p><?php endif; ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-white/10 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-700 dark:text-gray-300"><?= esc($n['note_type']) ?></span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"><?= esc($n['body']) ?></p>
                                    <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500"><?= esc($n['staff_name'] ?: '—') ?> · <?= esc(date('M j, Y H:i', strtotime($n['created_at']))) ?></p>
                                </div>
                                <form method="POST" action="<?= site_url('admin/customers/' . (int)$row['id'] . '/notes/' . (int)$n['id']) ?>" onsubmit="return confirm('Delete this note?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="size-3.5"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Allergies tab ── -->
        <div x-show="tab === 'allergies'" x-cloak class="space-y-4">
            <form method="POST" action="<?= site_url('admin/customers/' . (int)$row['id'] . '/allergies') ?>" class="rounded-lg bg-white dark:bg-gray-800 p-5 ring-1 ring-gray-200 dark:ring-white/10 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <?= csrf_field() ?>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Allergy / sensitivity</label>
                    <input name="allergy_name" required placeholder="e.g. Ammonia, sulfates" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Severity</label>
                    <select name="severity" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                        <option value="mild">Mild</option>
                        <option value="moderate">Moderate</option>
                        <option value="severe">Severe</option>
                    </select>
                </div>
                <div class="flex items-end"><button class="w-full inline-flex items-center justify-center gap-1 rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"><i data-lucide="plus" class="size-3.5"></i> Add</button></div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Notes (optional)</label>
                    <input name="notes" placeholder="Trigger / reaction details" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                </div>
            </form>

            <?php if (empty($allergies)): ?>
                <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-6 text-center text-sm text-gray-500 dark:text-gray-400">No allergies recorded.</div>
            <?php else: ?>
                <ul class="divide-y divide-gray-100 dark:divide-white/10 rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10">
                    <?php foreach ($allergies as $al): $sc = $severityCfg[$al['severity']] ?? ['color' => 'gray', 'label' => $al['severity']]; ?>
                        <li class="px-4 py-3 flex items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-<?= $sc['color'] ?>-100 dark:bg-<?= $sc['color'] ?>-500/20 text-<?= $sc['color'] ?>-700 dark:text-<?= $sc['color'] ?>-300 px-2 py-0.5 text-[10px] font-semibold uppercase"><?= esc($sc['label']) ?></span>
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm"><?= esc($al['allergy_name']) ?></p>
                                </div>
                                <?php if (! empty($al['notes'])): ?><p class="mt-1 text-xs text-gray-600 dark:text-gray-400"><?= esc($al['notes']) ?></p><?php endif; ?>
                            </div>
                            <form method="POST" action="<?= site_url('admin/customers/' . (int)$row['id'] . '/allergies/' . (int)$al['id']) ?>" onsubmit="return confirm('Remove this allergy?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="size-3.5"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- ── Preferences tab ── -->
        <div x-show="tab === 'preferences'" x-cloak class="space-y-4">
            <form method="POST" action="<?= site_url('admin/customers/' . (int)$row['id'] . '/preferences') ?>" class="rounded-lg bg-white dark:bg-gray-800 p-5 ring-1 ring-gray-200 dark:ring-white/10 space-y-3">
                <?= csrf_field() ?>
                <p class="text-xs text-gray-500 dark:text-gray-400">Things to remember about this customer — preferred drink, music, products, etc.</p>
                <?php if (! empty($preferences)): foreach ($preferences as $p): ?>
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <input value="<?= esc($p['preference_key']) ?>" disabled class="col-span-4 rounded-md border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-sm font-medium">
                        <input name="preferences[<?= esc($p['preference_key']) ?>]" value="<?= esc($p['preference_value']) ?>" class="col-span-7 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                        <a href="<?= site_url('admin/customers/' . (int)$row['id'] . '/preferences/' . (int)$p['id']) ?>" onclick="event.preventDefault(); if(confirm('Remove this preference?')) { const f=document.createElement('form'); f.method='POST'; f.action=this.href; f.innerHTML='<input name=\'<?= csrf_token() ?>\' value=\'<?= csrf_hash() ?>\'><input name=\'_method\' value=\'DELETE\'>'; document.body.appendChild(f); f.submit(); }" class="col-span-1 text-center text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="size-3.5"></i></a>
                    </div>
                <?php endforeach; endif; ?>
                <div class="grid grid-cols-12 gap-2 items-center pt-2 border-t border-gray-100 dark:border-white/10">
                    <input name="new_pref_key" placeholder="New key (e.g. Drink)" class="col-span-4 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                    <input name="new_pref_value" placeholder="Value (e.g. Black coffee)" class="col-span-7 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                    <div class="col-span-1"></div>
                </div>
                <div class="flex justify-end pt-2"><button class="inline-flex items-center gap-1 rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"><i data-lucide="check" class="size-3.5"></i> Save</button></div>
            </form>
        </div>

        <!-- ── Photos tab ── -->
        <div x-show="tab === 'photos'" x-cloak class="space-y-4">
            <?php if (empty($photoRows)): ?>
                <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-6 text-center text-sm text-gray-500 dark:text-gray-400">No before/after photos yet. Upload them from the <strong>Service history</strong> tab when editing a treatment.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($photoRows as $h): ?>
                        <div class="rounded-lg bg-white dark:bg-gray-800 p-4 ring-1 ring-gray-200 dark:ring-white/10">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1"><?= esc($h['service_name']) ?></p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-3"><?= esc(date('M j, Y', strtotime($h['service_date']))) ?> · <?= esc($h['staff_name'] ?: '—') ?></p>
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach (['before_image' => 'Before', 'after_image' => 'After'] as $field => $label): ?>
                                    <div>
                                        <p class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1"><?= $label ?></p>
                                        <?php if (! empty($h[$field])): ?>
                                            <a href="<?= base_url('uploads/' . $h[$field]) ?>" target="_blank" class="block rounded-md overflow-hidden ring-1 ring-gray-200 dark:ring-white/10">
                                                <img src="<?= base_url('uploads/' . $h[$field]) ?>" alt="<?= $label ?>" class="w-full h-40 object-cover">
                                            </a>
                                        <?php else: ?>
                                            <div class="rounded-md bg-gray-50 dark:bg-white/5 h-40 flex items-center justify-center text-xs text-gray-400 dark:text-gray-500">No <?= strtolower($label) ?> photo</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Files tab ── -->
        <div x-show="tab === 'files'" x-cloak class="space-y-4">
            <form method="POST" enctype="multipart/form-data" action="<?= site_url('admin/customers/' . (int)$row['id'] . '/files') ?>" class="rounded-lg bg-white dark:bg-gray-800 p-5 ring-1 ring-gray-200 dark:ring-white/10 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <?= csrf_field() ?>
                <div class="sm:col-span-5">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">File</label>
                    <input type="file" name="file" required class="mt-1 block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-brand-50 dark:file:bg-brand-500/15 file:text-brand-700 dark:file:text-brand-300 file:font-semibold">
                </div>
                <div class="sm:col-span-5">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Label (optional)</label>
                    <input name="label" placeholder="Consent form, reference image…" class="mt-1 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm">
                </div>
                <div class="sm:col-span-2"><button class="w-full inline-flex items-center justify-center gap-1 rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700"><i data-lucide="upload" class="size-3.5"></i> Upload</button></div>
            </form>

            <?php if (empty($files)): ?>
                <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-6 text-center text-sm text-gray-500 dark:text-gray-400">No files yet.</div>
            <?php else: ?>
                <ul class="divide-y divide-gray-100 dark:divide-white/10 rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/10">
                    <?php foreach ($files as $f): ?>
                        <li class="px-4 py-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="flex size-9 items-center justify-center rounded-md bg-brand-50 dark:bg-brand-500/15 text-brand-600 dark:text-brand-400 shrink-0"><i data-lucide="file" class="size-4"></i></span>
                                <div class="min-w-0">
                                    <a href="<?= base_url('uploads/' . $f['file_path']) ?>" target="_blank" class="block text-sm font-semibold text-gray-900 dark:text-white hover:text-brand-600 truncate"><?= esc($f['label'] ?: $f['file_name']) ?></a>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400"><?= esc(date('M j, Y H:i', strtotime($f['created_at']))) ?> · <?= number_format(($f['size_bytes'] ?? 0) / 1024, 1) ?> KB</p>
                                </div>
                            </div>
                            <form method="POST" action="<?= site_url('admin/customers/' . (int)$row['id'] . '/files/' . (int)$f['id']) ?>" onsubmit="return confirm('Delete this file?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="size-3.5"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
