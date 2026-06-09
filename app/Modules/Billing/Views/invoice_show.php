<?php
$statusColors = ['draft'=>'gray','unpaid'=>'amber','partial'=>'blue','paid'=>'green','refunded'=>'purple','cancelled'=>'gray'];
$c = $statusColors[$inv['status']] ?? 'gray';
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?= esc($inv['invoice_no']) ?></h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Created <?= esc(date('M j, Y H:i', strtotime($inv['created_at']))) ?></p>
                </div>
                <span class="inline-flex items-center rounded-full bg-<?= $c ?>-100 dark:bg-<?= $c ?>-500/20 px-2.5 py-0.5 text-xs font-medium text-<?= $c ?>-700 dark:text-<?= $c ?>-300 capitalize"><?= esc($inv['status']) ?></span>
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500 dark:text-gray-400">Bill to</dt><dd class="font-medium text-gray-900 dark:text-white"><?= esc($inv['customer_name']) ?></dd><dd class="text-xs text-gray-500 dark:text-gray-400"><?= esc($inv['customer_mobile']) ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Stylist</dt><dd class="text-gray-900 dark:text-white"><?= esc($inv['staff_name'] ?: '—') ?></dd></div>
            </dl>

            <table class="mt-6 min-w-full text-sm">
                <thead class="border-b border-gray-200 dark:border-white/10">
                    <tr class="text-left text-gray-500 dark:text-gray-400"><th class="py-1.5">Item</th><th>Qty</th><th>Unit</th><th>Tax%</th><th class="text-right">Total</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php foreach ($items as $it): ?>
                        <tr><td class="py-2"><?= esc($it['name']) ?></td><td><?= rtrim(rtrim((string)$it['qty'],'0'),'.') ?></td><td><?= number_format((float)$it['unit_price'], 2) ?></td><td><?= number_format((float)$it['tax_pct'], 2) ?></td><td class="text-right"><?= number_format((float)$it['line_total'], 2) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-4 ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Subtotal</span><span class="text-gray-900 dark:text-white">LKR <?= number_format((float)$inv['subtotal'], 2) ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Discount</span><span class="text-gray-900 dark:text-white">- LKR <?= number_format((float)$inv['discount'], 2) ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Tax</span><span class="text-gray-900 dark:text-white">LKR <?= number_format((float)$inv['tax'], 2) ?></span></div>
                <div class="flex justify-between font-semibold border-t pt-1.5"><span>Total</span><span>LKR <?= number_format((float)$inv['total'], 2) ?></span></div>
                <div class="flex justify-between text-green-600 dark:text-green-400"><span>Paid</span><span>LKR <?= number_format((float)$inv['paid'], 2) ?></span></div>
                <div class="flex justify-between font-semibold <?= $inv['balance']>0?'text-red-600 dark:text-red-400':'text-green-600 dark:text-green-400' ?>"><span>Balance</span><span>LKR <?= number_format((float)$inv['balance'], 2) ?></span></div>
            </div>
        </div>

        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Payment history</h3>
            <?php if (empty($payments)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400">No payments recorded.</p>
            <?php else: ?>
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-gray-500 dark:text-gray-400"><th class="py-1">Date</th><th>Method</th><th>Ref</th><th>Receipt</th><th class="text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td class="py-2"><?= esc(date('M j, H:i', strtotime($p['paid_at']))) ?></td>
                            <td><?= esc(str_replace('_',' ', $p['method'])) ?></td>
                            <td class="text-gray-500 dark:text-gray-400"><?= esc($p['txn_ref'] ?: '—') ?></td>
                            <td>
                                <?php if (! empty($p['receipt_path'])): ?>
                                    <a href="<?= base_url('uploads/' . $p['receipt_path']) ?>" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1 text-brand-600 dark:text-brand-400 hover:underline">
                                        <i data-lucide="paperclip" class="size-3.5"></i> View
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">LKR <?= number_format((float)$p['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="space-y-4">
        <!-- Stylist & appointment attribution -->
        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center gap-2 mb-1">
                <span class="flex size-7 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                    <i data-lucide="user-check" class="size-4"></i>
                </span>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Stylist &amp; appointment</h3>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Attribute this invoice so it shows in the stylist's revenue &amp; payout reports.</p>
            <form method="POST" action="<?= site_url('admin/billing/invoices/'.$inv['id'].'/attribution') ?>" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label for="attr_staff" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Stylist</label>
                    <select id="attr_staff" name="staff_id"
                            class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">— Unassigned —</option>
                        <?php foreach (($staff ?? []) as $st): ?>
                            <option value="<?= (int)$st['id'] ?>" <?= (int)($inv['staff_id'] ?? 0) === (int)$st['id'] ? 'selected' : '' ?>>
                                <?= esc($st['full_name']) ?><?= !empty($st['role']) ? ' · '.esc($st['role']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="attr_appt" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Linked appointment <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span>
                    </label>
                    <select id="attr_appt" name="appointment_id"
                            class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">— None —</option>
                        <?php foreach (($appointments ?? []) as $ap): ?>
                            <option value="<?= (int)$ap['id'] ?>" <?= (int)($inv['appointment_id'] ?? 0) === (int)$ap['id'] ? 'selected' : '' ?>>
                                <?= esc($ap['code'] ?: ('#'.$ap['id'])) ?> · <?= esc(date('M j, Y H:i', strtotime($ap['start_at']))) ?><?= !empty($ap['staff_name']) ? ' · '.esc($ap['staff_name']) : '' ?> · <?= esc(ucwords(str_replace('_',' ', $ap['status']))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Only this customer's appointments are listed. Linking one with a stylist set will use that stylist.</p>
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    <i data-lucide="save" class="size-4"></i> Save attribution
                </button>
            </form>
        </div>

        <?php if ($loyaltyEnabled && $customerPoints >= $minRedeem && (float)$inv['balance'] > 0): ?>
            <?php
            $maxLkr = max(0, (float)$inv['total'] - (float)$inv['discount'] - (float)$inv['paid']);
            $maxPts = min($customerPoints, (int) floor($maxLkr / max($redeemValue, 0.01)));
            ?>
            <div class="rounded-lg bg-gradient-to-br from-amber-50 to-brand-50 dark:from-amber-500/10 dark:to-brand-500/10 p-5 ring-1 ring-amber-200 dark:ring-amber-500/20"
                 x-data='{ pts: <?= $maxPts ?> }'>
                <div class="flex items-center gap-2.5 mb-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300">
                        <i data-lucide="award" class="size-5"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Redeem loyalty points</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Customer has <strong class="text-amber-700 dark:text-amber-300"><?= $customerPoints ?> pts</strong> · max usable <strong><?= $maxPts ?> pts</strong></p>
                    </div>
                </div>
                <form method="POST" action="<?= site_url('admin/billing/invoices/'.$inv['id'].'/redeem') ?>" class="space-y-2">
                    <?= csrf_field() ?>
                    <input type="range" min="<?= $minRedeem ?>" max="<?= $maxPts ?>" step="<?= max(1, (int)floor($minRedeem / 5)) ?>"
                           x-model.number="pts" name="points"
                           class="w-full accent-brand-600">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-700 dark:text-gray-300"><strong x-text="pts"></strong> points</span>
                        <span class="text-brand-700 dark:text-brand-300 font-semibold">= LKR <strong x-text="(pts * <?= $redeemValue ?>).toFixed(0)"></strong> off</span>
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                        <i data-lucide="ticket-percent" class="size-4"></i> Apply discount
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Record payment</h3>
            <form method="POST" action="<?= site_url('admin/billing/invoices/'.$inv['id'].'/payments') ?>" enctype="multipart/form-data" class="space-y-3" x-data="{ method: 'cash' }">
                <?= csrf_field() ?>
                <?= view('components/form/input',['name'=>'amount','label'=>'Amount','type'=>'number','required'=>true,'value'=>number_format((float)$inv['balance'], 2, '.', ''),'attrs'=>['step'=>'0.01']]) ?>

                <div>
                    <label for="pay_method" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Method</label>
                    <select id="pay_method" name="method" required x-model="method"
                            class="mt-2 block w-full rounded-md border-gray-300 py-1.5 pl-3 pr-8 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank transfer</option>
                        <option value="mobile_wallet">Mobile wallet</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <?= view('components/form/input',['name'=>'txn_ref','label'=>'Reference (optional)']) ?>

                <!-- Receipt upload — shown only for bank transfer -->
                <div x-show="method === 'bank_transfer'" x-cloak x-transition class="rounded-md bg-gray-50 dark:bg-white/5 p-3 ring-1 ring-gray-200 dark:ring-white/10">
                    <label for="receipt" class="flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <i data-lucide="paperclip" class="size-3.5"></i> Attach payment receipt
                    </label>
                    <input type="file" id="receipt" name="receipt" accept="image/*,application/pdf"
                           class="block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700">
                    <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Image or PDF of the bank-transfer slip, up to 5 MB.</p>
                </div>

                <button class="w-full rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">Record payment</button>
            </form>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow ring-1 ring-gray-200 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Share & print</h3>
            <?php
            $waPhone = preg_replace('/[^0-9]/', '', (string)($inv['customer_mobile'] ?? ''));
            $waMsg = "Hi " . $inv['customer_name'] . ",\nYour invoice " . $inv['invoice_no']
                . " is ready: " . site_url('admin/billing/invoices/' . $inv['id'] . '/pdf')
                . "\nTotal: LKR " . number_format((float)$inv['total'], 2);
            $waUrl = 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($waMsg);
            ?>
            <div class="grid grid-cols-2 gap-2"
                 x-data="{ emailOpen: false, emailTo: '<?= esc($inv['customer_email'] ?? '', 'attr') ?>' }">

                <a href="<?= site_url('admin/billing/invoices/'.$inv['id'].'/pdf') ?>"
                   class="col-span-2 inline-flex items-center justify-center gap-2 rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm shadow-brand-600/20">
                    <i data-lucide="file-down" class="size-4"></i> Download PDF
                </a>

                <a href="<?= site_url('admin/billing/invoices/'.$inv['id'].'/print') ?>" target="_blank"
                   class="inline-flex items-center justify-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                    <i data-lucide="printer" class="size-4"></i> Print
                </a>

                <button type="button" @click="emailOpen = !emailOpen"
                        class="inline-flex items-center justify-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10">
                    <i data-lucide="mail" class="size-4"></i> Email
                </button>

                <?php if ($waPhone): ?>
                    <a href="<?= esc($waUrl) ?>" target="_blank" rel="noopener"
                       class="col-span-2 inline-flex items-center justify-center gap-2 rounded-md bg-[#25D366] px-3 py-2 text-sm font-semibold text-white hover:bg-[#1ebe57]">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-4"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 2.1.55 4.15 1.6 5.96L2 22l4.25-1.11a9.9 9.9 0 004.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.84 9.84 0 0012.04 2zm0 18.13a8.2 8.2 0 01-4.18-1.14l-.3-.18-2.5.65.67-2.44-.2-.31a8.22 8.22 0 1115.27-4.36c0 4.53-3.69 8.22-8.21 8.22zm4.5-6.16c-.25-.12-1.46-.72-1.69-.8-.23-.08-.4-.13-.56.13-.17.25-.64.8-.78.96-.14.17-.29.19-.54.06-.25-.12-1.04-.38-1.98-1.22a7.48 7.48 0 01-1.38-1.72c-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.87.85-.87 2.07s.89 2.4 1.02 2.57c.13.17 1.76 2.69 4.26 3.77.6.26 1.06.41 1.42.53.6.19 1.14.16 1.57.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.23-.16-.48-.28z"/></svg>
                        Share via WhatsApp
                    </a>
                <?php else: ?>
                    <div class="col-span-2 rounded-md bg-gray-50 dark:bg-white/5 px-3 py-2 text-xs text-gray-500 dark:text-gray-400 text-center">
                        Add a mobile to <?= esc($inv['customer_name']) ?> for WhatsApp share
                    </div>
                <?php endif; ?>

                <!-- Inline email form -->
                <div x-show="emailOpen" x-cloak x-transition class="col-span-2 mt-1 rounded-md bg-gray-50 dark:bg-white/5 p-3">
                    <form method="POST" action="<?= site_url('admin/billing/invoices/'.$inv['id'].'/email') ?>" class="space-y-2">
                        <?= csrf_field() ?>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Send PDF to</label>
                        <div class="flex gap-2">
                            <input type="email" name="to" x-model="emailTo" required placeholder="customer@example.com"
                                   class="flex-1 rounded-md border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm focus:border-brand-500 focus:ring-brand-500">
                            <button type="submit" class="inline-flex items-center gap-1 rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                                <i data-lucide="send" class="size-3.5"></i> Send
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Uses SMTP from Settings → SMTP.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
