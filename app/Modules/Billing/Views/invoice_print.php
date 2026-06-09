<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= esc($inv['invoice_no']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8 print:bg-white">
<div class="max-w-2xl mx-auto bg-white p-8 shadow print:shadow-none">
    <div class="flex items-start justify-between border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-pink-600">SalonCMS</h1>
            <p class="text-sm text-gray-500">Salon Appointment & Billing</p>
        </div>
        <div class="text-right">
            <h2 class="text-lg font-semibold">INVOICE</h2>
            <p class="text-sm text-gray-700"><?= esc($inv['invoice_no']) ?></p>
            <p class="text-xs text-gray-500"><?= esc(date('M j, Y H:i', strtotime($inv['created_at']))) ?></p>
        </div>
    </div>
    <div class="mt-4 text-sm">
        <p class="text-gray-500">Bill To:</p>
        <p class="font-medium"><?= esc($inv['customer_name']) ?></p>
        <p class="text-gray-600"><?= esc($inv['customer_mobile']) ?></p>
    </div>
    <table class="mt-6 w-full text-sm border-t">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Item</th><th>Qty</th><th>Unit</th><th class="text-right">Total</th></tr></thead>
        <tbody class="divide-y">
            <?php foreach ($items as $it): ?>
                <tr><td class="py-2"><?= esc($it['name']) ?></td><td><?= rtrim(rtrim((string)$it['qty'],'0'),'.') ?></td><td><?= number_format((float)$it['unit_price'], 2) ?></td><td class="text-right"><?= number_format((float)$it['line_total'], 2) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="mt-4 ml-auto max-w-xs text-sm">
        <div class="flex justify-between"><span>Subtotal</span><span>LKR <?= number_format((float)$inv['subtotal'], 2) ?></span></div>
        <div class="flex justify-between"><span>Discount</span><span>- LKR <?= number_format((float)$inv['discount'], 2) ?></span></div>
        <div class="flex justify-between"><span>Tax</span><span>LKR <?= number_format((float)$inv['tax'], 2) ?></span></div>
        <div class="flex justify-between font-semibold border-t pt-1.5 mt-1.5"><span>Total</span><span>LKR <?= number_format((float)$inv['total'], 2) ?></span></div>
        <div class="flex justify-between text-green-600"><span>Paid</span><span>LKR <?= number_format((float)$inv['paid'], 2) ?></span></div>
        <div class="flex justify-between font-semibold"><span>Balance</span><span>LKR <?= number_format((float)$inv['balance'], 2) ?></span></div>
    </div>
    <p class="mt-8 text-center text-xs text-gray-500">Thank you for visiting SalonCMS!</p>
    <div class="mt-6 text-center print:hidden"><button onclick="window.print()" class="rounded-md bg-pink-600 px-4 py-2 text-white text-sm font-semibold">Print</button></div>
</div>
</body>
</html>
