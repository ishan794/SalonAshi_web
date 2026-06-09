<?php
$statusColors = [
    'pending' => 'amber','confirmed' => 'blue','checked_in' => 'cyan',
    'in_progress' => 'indigo','completed' => 'green','cancelled' => 'gray','no_show' => 'red',
];
$c = $statusColors[$row['status']] ?? 'gray';
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?= esc($row['code']) ?></h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Booked for <?= esc(date('l, F j, Y · H:i', strtotime($row['start_at']))) ?></p>
                </div>
                <span class="inline-flex items-center rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 px-2.5 py-0.5 text-xs font-medium text-<?= $c ?>-700 dark:text-<?= $c ?>-300"><?= esc(str_replace('_',' ', $row['status'])) ?></span>
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500 dark:text-gray-400">Customer</dt><dd class="font-medium text-gray-900 dark:text-white"><?= esc($row['customer_name']) ?></dd><dd class="text-xs text-gray-500 dark:text-gray-400"><?= esc($row['customer_mobile']) ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Stylist</dt><dd class="font-medium text-gray-900 dark:text-white"><?= esc($row['staff_name']) ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Start</dt><dd class="text-gray-900 dark:text-white"><?= esc($row['start_at']) ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">End</dt><dd class="text-gray-900 dark:text-white"><?= esc($row['end_at']) ?></dd></div>
            </dl>

            <h3 class="mt-6 text-sm font-semibold text-gray-900 dark:text-white">Services</h3>
            <table class="mt-2 min-w-full text-sm">
                <thead class="border-b border-gray-200 dark:border-white/10"><tr class="text-left text-gray-500 dark:text-gray-400"><th class="py-1.5">Service</th><th>Duration</th><th>Price</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php foreach ($row['services'] as $s): ?>
                    <tr><td class="py-2"><?= esc($s['service_name']) ?></td><td><?= (int)$s['duration_min'] ?> min</td><td>LKR <?= number_format((float)$s['price'], 2) ?></td></tr>
                <?php endforeach; ?>
                <tr class="font-semibold"><td colspan="2" class="py-2 text-right">Subtotal</td><td>LKR <?= number_format((float)$row['subtotal'], 2) ?></td></tr>
                </tbody>
            </table>

            <?php if (!empty($row['notes'])): ?>
                <div class="mt-4 rounded-md bg-gray-50 dark:bg-white/5 p-3 text-sm text-gray-700 dark:text-gray-300"><strong>Notes:</strong> <?= esc($row['notes']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="space-y-4">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Update status</h3>
            <form method="POST" action="<?= site_url('admin/appointments/'.$row['id'].'/status') ?>" class="space-y-2">
                <?= csrf_field() ?>
                <select name="status" class="w-full rounded-md border-gray-300 dark:border-white/10 text-sm focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 dark:bg-white/5 dark:text-white">
                    <?php foreach (['pending','confirmed','checked_in','in_progress','completed','cancelled','no_show'] as $s): ?>
                        <option value="<?= $s ?>" <?= $row['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ', $s)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="w-full rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">Update</button>
            </form>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10 space-y-2"
             x-data='{ cancelOpen:false, cancelType:"cancelled", cancelBy:"customer", cancelReason:"", cancelFee:0 }'>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Actions</h3>
            <a href="<?= site_url('admin/appointments/'.$row['id'].'/edit') ?>" class="block w-full text-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 ring-1 ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5">Edit</a>
            <a href="<?= site_url('admin/billing/invoices/create-from-appointment/'.$row['id']) ?>" class="block w-full text-center rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">Generate Invoice</a>

            <button type="button" @click='cancelType="cancelled"; cancelOpen=true' class="block w-full text-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-3 py-2 text-sm font-medium text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-500/20">
                <i data-lucide="ban" class="inline size-4 mr-1"></i> Record cancellation
            </button>
            <button type="button" @click='cancelType="no_show"; cancelOpen=true' class="block w-full text-center rounded-md bg-red-50 dark:bg-red-500/10 px-3 py-2 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-500/20">
                <i data-lucide="user-x" class="inline size-4 mr-1"></i> Mark as no-show
            </button>

            <form method="POST" action="<?= site_url('admin/appointments/'.$row['id']) ?>" onsubmit="return confirm('Delete this appointment?');">
                <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                <button class="w-full rounded-md bg-white dark:bg-white/5 ring-1 ring-gray-300 dark:ring-white/10 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/10">Delete</button>
            </form>

            <!-- ── Cancel/No-show modal ── -->
            <div x-show="cancelOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="cancelOpen=false">
                <div class="fixed inset-0 bg-gray-900/70 dark:bg-gray-950/85 backdrop-blur-sm" @click="cancelOpen=false"></div>
                <div class="relative w-full max-w-md rounded-xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-gray-200 dark:ring-white/10"
                     x-transition:enter="ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <form method="POST" action="<?= site_url('admin/appointments/'.$row['id'].'/cancel') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="type" :value="cancelType">

                        <div class="flex items-start justify-between gap-3 border-b border-gray-200 dark:border-white/10 px-5 py-3.5">
                            <div class="flex items-start gap-3">
                                <span :class="cancelType === 'no_show' ? 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300'"
                                      class="flex size-9 items-center justify-center rounded-md">
                                    <i :data-lucide="cancelType === 'no_show' ? 'user-x' : 'ban'" class="size-4"></i>
                                </span>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white"
                                        x-text="cancelType === 'no_show' ? 'Mark as no-show' : 'Record cancellation'"></h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">This will update the customer's reliability stats.</p>
                                </div>
                            </div>
                            <button type="button" @click="cancelOpen=false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"><i data-lucide="x" class="size-5"></i></button>
                        </div>

                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Cancelled by</label>
                                <div class="mt-1.5 grid grid-cols-3 gap-1.5">
                                    <template x-for="opt in ['customer','staff','system']" :key="opt">
                                        <button type="button" @click="cancelBy=opt"
                                                :class="cancelBy === opt ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10'"
                                                class="rounded-md px-2 py-1.5 text-xs font-medium capitalize" x-text="opt"></button>
                                    </template>
                                </div>
                                <input type="hidden" name="cancelled_by" :value="cancelBy">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Reason <span class="text-red-500">*</span></label>
                                <textarea name="reason" required x-model="cancelReason" rows="2" placeholder="e.g. customer didn't show up · sick · double-booked · no answer to reminder…"
                                          class="mt-1.5 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Cancellation fee charged (LKR)</label>
                                <input type="number" name="fee" min="0" step="0.01" x-model.number="cancelFee" placeholder="0"
                                       class="mt-1.5 w-full rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Leave 0 if no fee was applied.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 border-t border-gray-200 dark:border-white/10 px-5 py-3 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" @click="cancelOpen=false" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">Cancel</button>
                            <button type="submit"
                                    :class="cancelType === 'no_show' ? 'bg-red-600 hover:bg-red-700' : 'bg-amber-600 hover:bg-amber-700'"
                                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-semibold text-white shadow-sm">
                                <i data-lucide="check" class="size-4"></i>
                                <span x-text="cancelType === 'no_show' ? 'Record no-show' : 'Record cancellation'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
