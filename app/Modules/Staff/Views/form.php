<?php
$isEdit = !empty($row);
$action = $isEdit ? site_url('admin/staff/' . $row['id']) : site_url('admin/staff');
$staff  = $row ?: ['full_name' => '', 'role' => '', 'is_active' => 1, 'commission_pct' => 0, 'email' => ''];
$activeTab = 'profile';
?>
<div class="max-w-4xl space-y-6">
<?php if ($isEdit) include __DIR__ . '/_tabs.php'; ?>
<form method="POST" action="<?= $action ?>" class="space-y-6">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-5">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white"><?= $isEdit ? 'Edit' : 'New' ?> Staff</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <?= view('components/form/input',['name'=>'full_name','label'=>'Full name','required'=>true,'value'=>$row['full_name'] ?? '']) ?>
            <?= view('components/form/input',['name'=>'role','label'=>'Job role','placeholder'=>'e.g. Senior Stylist','value'=>$row['role'] ?? '']) ?>
            <?= view('components/form/input',['name'=>'mobile','label'=>'Mobile','value'=>$row['mobile'] ?? '']) ?>
            <?= view('components/form/input',['name'=>'email','label'=>'Email','type'=>'email','value'=>$row['email'] ?? '']) ?>
            <?= view('components/form/input',['name'=>'working_hours','label'=>'Working hours','placeholder'=>'09:00-18:00','value'=>$row['working_hours'] ?? '']) ?>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" <?= !$isEdit || !empty($row['is_active']) ? 'checked' : '' ?> class="rounded border-gray-300 dark:border-white/10 text-brand-600 dark:text-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400">
            Active
        </label>
    </div>

    <!-- ── Payout & bank details ── -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-5"
         x-data="{ method: '<?= esc($row['payout_method'] ?? '', 'js') ?>' }">
        <div class="flex items-center gap-2.5">
            <span class="flex size-8 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                <i data-lucide="hand-coins" class="size-4"></i>
            </span>
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Payout &amp; bank details</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">How this staff member is paid. Bank details print on the payout statement.</p>
            </div>
        </div>

        <!-- Payout settings -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label for="payout_method" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Payout method</label>
                <select id="payout_method" name="payout_method" x-model="method"
                        class="mt-2 block w-full rounded-md border-gray-300 py-1.5 pl-3 pr-8 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    <?php $pm = $row['payout_method'] ?? ''; foreach (['' => '— Not set —','bank_transfer'=>'Bank transfer','cash'=>'Cash','cheque'=>'Cheque','mobile_wallet'=>'Mobile wallet'] as $v => $t): ?>
                        <option value="<?= esc($v) ?>" <?= $pm === $v ? 'selected' : '' ?>><?= esc($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="payout_frequency" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Payout frequency</label>
                <select id="payout_frequency" name="payout_frequency"
                        class="mt-2 block w-full rounded-md border-gray-300 py-1.5 pl-3 pr-8 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    <?php $pf = $row['payout_frequency'] ?? ''; foreach (['' => '— Not set —','weekly'=>'Weekly','biweekly'=>'Bi-weekly','monthly'=>'Monthly'] as $v => $t): ?>
                        <option value="<?= esc($v) ?>" <?= $pf === $v ? 'selected' : '' ?>><?= esc($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?= view('components/form/input',['name'=>'commission_pct','label'=>'Commission %','type'=>'number','value'=>$row['commission_pct'] ?? '0','attrs'=>['step'=>'0.01'],'helpText'=>'Share of revenue paid out']) ?>
        </div>

        <!-- Bank account fields (most relevant for bank transfer) -->
        <div x-show="method === 'bank_transfer' || method === '' || method === 'cheque'" x-transition>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <?= view('components/form/input',['name'=>'bank_name','label'=>'Bank name','placeholder'=>'e.g. Commercial Bank','value'=>$row['bank_name'] ?? '']) ?>
                <?= view('components/form/input',['name'=>'bank_branch','label'=>'Branch','placeholder'=>'e.g. Colombo 03','value'=>$row['bank_branch'] ?? '']) ?>
                <?= view('components/form/input',['name'=>'bank_account_name','label'=>'Account holder name','value'=>$row['bank_account_name'] ?? '']) ?>
                <?= view('components/form/input',['name'=>'bank_account_no','label'=>'Account number','value'=>$row['bank_account_no'] ?? '']) ?>
                <?= view('components/form/input',['name'=>'bank_code','label'=>'Bank / branch code','placeholder'=>'SLIPS code (optional)','value'=>$row['bank_code'] ?? '']) ?>
            </div>
        </div>

        <?= view('components/form/input',['name'=>'payout_notes','label'=>'Payout notes (optional)','placeholder'=>'e.g. pays end of month, deduct advances','value'=>$row['payout_notes'] ?? '']) ?>
    </div>

    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-6">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Assigned services</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Select the services this staff member is trained and available to perform.</p>
        </div>
        
        <?php
        // Group services by category
        $groupedServices = [];
        foreach ($allServices as $s) {
            $cat = $s['category_name'] ?: 'Uncategorised';
            $groupedServices[$cat][] = $s;
        }
        ?>

        <div class="space-y-6">
            <?php foreach ($groupedServices as $categoryName => $services): ?>
                <div class="space-y-2.5">
                    <!-- Category Header -->
                    <div class="flex items-center gap-2 border-b border-gray-100 dark:border-white/5 pb-2">
                        <span class="size-1.5 rounded-full bg-brand-500"></span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400"><?= esc($categoryName) ?></h4>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">(<?= count($services) ?>)</span>
                    </div>
                    
                    <!-- Services Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        <?php foreach ($services as $s): ?>
                            <label class="flex items-center gap-2 rounded-md ring-1 ring-gray-200 dark:ring-white/10 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5 cursor-pointer transition-colors duration-150">
                                <input type="checkbox" name="service_ids[]" value="<?= (int)$s['id'] ?>" <?= in_array($s['id'], $assigned) ? 'checked' : '' ?> class="rounded border-gray-300 dark:border-white/10 text-brand-600 dark:text-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400">
                                <span class="truncate text-gray-700 dark:text-gray-300"><?= esc($s['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($isEdit && !empty($schedule)):
        $dowNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    ?>
    <!-- ── Weekly schedule ── -->
    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Working schedule</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Hours used by the online booking and POS availability checker.</p>
            </div>
            <a href="<?= site_url('admin/staff/'.$row['id'].'/calendar') ?>" class="inline-flex items-center gap-1.5 rounded-md bg-white dark:bg-white/5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                <i data-lucide="calendar-days" class="size-3.5"></i> View calendar
            </a>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-white/5">
            <?php for ($d = 0; $d < 7; $d++): $r = $schedule[$d]; ?>
                <div class="py-2.5 grid grid-cols-12 gap-2 items-center" x-data='{ off: <?= !empty($r['is_off']) ? 'true' : 'false' ?> }'>
                    <div class="col-span-3 font-medium text-sm text-gray-900 dark:text-white"><?= esc($dowNames[$d]) ?></div>
                    <div class="col-span-3">
                        <input type="time" name="schedule[<?= $d ?>][start_time]" :disabled="off"
                               value="<?= esc(substr($r['start_time'], 0, 5)) ?>"
                               :class="off ? 'opacity-40 cursor-not-allowed' : ''"
                               class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div class="col-span-1 text-center text-xs text-gray-400">to</div>
                    <div class="col-span-3">
                        <input type="time" name="schedule[<?= $d ?>][end_time]" :disabled="off"
                               value="<?= esc(substr($r['end_time'], 0, 5)) ?>"
                               :class="off ? 'opacity-40 cursor-not-allowed' : ''"
                               class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div class="col-span-2 text-right">
                        <label class="inline-flex items-center gap-1.5 text-xs cursor-pointer">
                            <input type="checkbox" name="schedule[<?= $d ?>][is_off]" value="1"
                                   x-model="off" <?= !empty($r['is_off']) ? 'checked' : '' ?>
                                   class="rounded border-gray-300 dark:border-white/10 text-brand-600 focus:ring-brand-500">
                            <span :class="off ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400'" x-text="off ? 'Off' : 'Open'"></span>
                        </label>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <?php endif; /* end isEdit-only schedule section */ ?>

    <div class="flex items-center justify-end gap-3">
        <a href="<?= site_url('admin/staff') ?>" class="rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5">Cancel</a>
        <button type="submit" class="rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700"><?= $isEdit ? 'Save changes' : 'Create' ?></button>
    </div>
</form>

<?php if ($isEdit): ?>
<!-- ── Time off (its own form, OUTSIDE the main staff form) ── -->
<div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Time off / vacation</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Specific dates the staff is unavailable. Overrides the weekly schedule.</p>
        </div>
    </div>

    <?php if (empty($timeOff)): ?>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No upcoming time off.</p>
    <?php else: ?>
        <ul class="divide-y divide-gray-100 dark:divide-white/5 mb-4">
            <?php foreach ($timeOff as $t): ?>
                <li class="py-2 flex items-center justify-between gap-3">
                    <div class="min-w-0 flex items-center gap-2">
                        <i data-lucide="calendar-off" class="size-4 text-red-500"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white"><?= esc(date('l, M j, Y', strtotime($t['off_date']))) ?></p>
                            <?php if (!empty($t['reason'])): ?><p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($t['reason']) ?></p><?php endif; ?>
                        </div>
                    </div>
                    <form method="POST" action="<?= site_url('admin/staff/'.$row['id'].'/time-off/'.$t['id']) ?>" onsubmit="return confirm('Remove this time-off?');">
                        <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                        <button class="text-xs text-red-600 dark:text-red-400 hover:text-red-700">Remove</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="<?= site_url('admin/staff/'.$row['id'].'/time-off') ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-2">
        <?= csrf_field() ?>
        <input type="date" name="off_date" required min="<?= date('Y-m-d') ?>"
               class="sm:col-span-4 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
        <input type="text" name="reason" placeholder="Reason (optional) — e.g. vacation, sick"
               class="sm:col-span-6 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
        <button class="sm:col-span-2 inline-flex items-center justify-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">
            <i data-lucide="calendar-plus" class="size-3.5"></i> Add
        </button>
    </form>
</div>

<!-- ── Per-date time slots (override weekly schedule for a specific date) ── -->
<?php
$hasOverride = !empty($dateWindows);
$displayWindows = $hasOverride ? array_map(fn($w) => [substr($w['start_time'],0,5), substr($w['end_time'],0,5)], $dateWindows) : ($fallbackWindows ?? []);
$dayOff = $fallbackWindows === null;
?>
<div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Date-specific time slots</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Override the weekly schedule for a specific date. The booking page will only offer slots inside these windows.</p>
        </div>
        <!-- Date selector (GET form) -->
        <form method="GET" action="<?= site_url('admin/staff/'.$row['id'].'/edit') ?>" class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Date:</label>
            <input type="date" name="date" value="<?= esc($pickDate) ?>"
                   onchange="this.form.submit()"
                   class="rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
        </form>
    </div>

    <!-- Status banner -->
    <div class="mb-4 rounded-md ring-1 p-3 <?= $dayOff ? 'bg-red-50 dark:bg-red-500/10 ring-red-200 dark:ring-red-500/30' : ($hasOverride ? 'bg-brand-50 dark:bg-brand-500/10 ring-brand-200 dark:ring-brand-500/30' : 'bg-gray-50 dark:bg-white/5 ring-gray-200 dark:ring-white/10') ?>">
        <div class="flex items-start gap-2.5">
            <i data-lucide="<?= $dayOff ? 'calendar-off' : ($hasOverride ? 'calendar-check' : 'calendar') ?>" class="size-4 mt-0.5 <?= $dayOff ? 'text-red-600 dark:text-red-400' : ($hasOverride ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400') ?>"></i>
            <div class="flex-1 min-w-0 text-sm">
                <p class="font-semibold text-gray-900 dark:text-white"><?= esc(date('l, F j, Y', strtotime($pickDate))) ?></p>
                <p class="text-xs <?= $dayOff ? 'text-red-700 dark:text-red-300' : ($hasOverride ? 'text-brand-700 dark:text-brand-300' : 'text-gray-600 dark:text-gray-400') ?>">
                    <?php if ($dayOff): ?>
                        Marked as full day off (from time-off list). Remove the time-off entry above to enable booking.
                    <?php elseif ($hasOverride): ?>
                        Using custom windows for this date (<?= count($dateWindows) ?>).
                    <?php else: ?>
                        Using the weekly schedule for <?= esc(date('l', strtotime($pickDate))) ?>.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Current windows -->
    <div class="space-y-2">
        <?php if (empty($displayWindows)): ?>
            <p class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">No working windows for this date.</p>
        <?php else: ?>
            <?php foreach ($displayWindows as $i => [$start, $end]):
                $isCustom = $hasOverride && isset($dateWindows[$i]);
            ?>
                <div class="flex items-center gap-3 rounded-md ring-1 ring-gray-200 dark:ring-white/10 px-3 py-2.5">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-brand-50 dark:bg-brand-500/15 text-brand-600 dark:text-brand-300">
                        <i data-lucide="clock" class="size-4"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white"><?= esc($start) ?> – <?= esc($end) ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <?php if ($isCustom): ?>
                                <?= esc($dateWindows[$i]['note'] ?: 'Custom window') ?>
                            <?php else: ?>
                                <em>From weekly schedule</em>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($isCustom): ?>
                        <form method="POST" action="<?= site_url('admin/staff/'.$row['id'].'/date-window/'.$dateWindows[$i]['id'].'?date='.$pickDate) ?>" onsubmit="return confirm('Remove this window?');">
                            <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                            <button class="inline-flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-700">
                                <i data-lucide="trash-2" class="size-3.5"></i> Remove
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 italic">read-only</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Add window form -->
    <?php if (! $dayOff): ?>
        <form method="POST" action="<?= site_url('admin/staff/'.$row['id'].'/date-window') ?>" class="mt-4 grid grid-cols-1 sm:grid-cols-12 gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="on_date" value="<?= esc($pickDate) ?>">
            <div class="sm:col-span-3">
                <label class="block text-[10px] uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400 mb-1">Start</label>
                <input type="time" name="start_time" required value="09:00"
                       class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[10px] uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400 mb-1">End</label>
                <input type="time" name="end_time" required value="13:00"
                       class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div class="sm:col-span-4">
                <label class="block text-[10px] uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400 mb-1">Note (optional)</label>
                <input type="text" name="note" placeholder="e.g. Morning shift, special hours"
                       class="w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div class="sm:col-span-2 flex items-end">
                <button class="w-full inline-flex items-center justify-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">
                    <i data-lucide="plus" class="size-3.5"></i> Add window
                </button>
            </div>
        </form>

        <?php if ($hasOverride): ?>
            <div class="mt-3 flex justify-end">
                <form method="POST" action="<?= site_url('admin/staff/'.$row['id'].'/date-window/reset') ?>" onsubmit="return confirm('Remove ALL custom windows for this date and revert to the weekly schedule?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="on_date" value="<?= esc($pickDate) ?>">
                    <button class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                        <i data-lucide="rotate-ccw" class="size-3.5"></i> Reset to weekly schedule
                    </button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php endif; ?>

</div>
