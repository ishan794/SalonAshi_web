<?php /** @var array $inv, $items, $payments; @var \App\Modules\Settings\Models\SettingModel $s */
$currency = $s->get('salon_currency', 'LKR');
$salonName = $s->get('salon_name', 'SalonCMS');
$address = $s->get('biz_address', '');
$phone = $s->get('biz_phone', '');
$email = $s->get('biz_email', '');
$logo = $s->get('biz_logo');
$logoPath = $logo ? FCPATH . 'uploads/' . $logo : null;
$logoBase64 = ($logoPath && is_file($logoPath))
    ? 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath))
    : null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 30px 36px; }
  body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: #1f2937; font-size: 12px; }
  .brand-pink { color: #db2777; }
  h1 { margin: 0; font-size: 22px; color: #db2777; }
  h2 { margin: 0; font-size: 18px; color: #111827; }
  .muted { color: #6b7280; font-size: 11px; }
  table { width: 100%; border-collapse: collapse; }
  table.hdr td { vertical-align: top; }
  table.items th, table.items td { padding: 8px 6px; }
  table.items thead th { border-bottom: 2px solid #e5e7eb; color: #6b7280; text-transform: uppercase; font-size: 10px; letter-spacing: .05em; text-align: left; }
  table.items tbody td { border-bottom: 1px solid #f3f4f6; }
  table.items .num { text-align: right; }
  table.totals { margin-top: 12px; }
  table.totals td { padding: 4px 6px; }
  table.totals tr.total td { font-weight: bold; border-top: 2px solid #e5e7eb; padding-top: 10px; font-size: 14px; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
  .badge-paid     { background: #d1fae5; color: #065f46; }
  .badge-unpaid   { background: #fef3c7; color: #92400e; }
  .badge-partial  { background: #dbeafe; color: #1e40af; }
  .badge-cancelled{ background: #f3f4f6; color: #4b5563; }
  .footer { margin-top: 30px; text-align: center; color: #9ca3af; font-size: 10px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
</style>
</head>
<body>

<table class="hdr">
  <tr>
    <td>
      <?php if ($logoBase64): ?>
        <img src="<?= $logoBase64 ?>" style="max-height:54px; max-width:160px;">
      <?php else: ?>
        <h1><?= esc($salonName) ?></h1>
      <?php endif; ?>
      <div class="muted">
        <?php if ($address): ?><?= nl2br(esc($address)) ?><br><?php endif; ?>
        <?php if ($phone): ?><?= esc($phone) ?><?php endif; ?>
        <?php if ($phone && $email): ?> · <?php endif; ?>
        <?php if ($email): ?><?= esc($email) ?><?php endif; ?>
      </div>
    </td>
    <td style="text-align:right;">
      <h2>INVOICE</h2>
      <div class="muted"><strong><?= esc($inv['invoice_no']) ?></strong></div>
      <div class="muted">Date: <?= esc(date('M j, Y', strtotime($inv['created_at']))) ?></div>
      <div style="margin-top:6px;">
        <span class="badge badge-<?= esc($inv['status']) ?>"><?= esc(strtoupper($inv['status'])) ?></span>
      </div>
    </td>
  </tr>
</table>

<table style="margin-top:22px;">
  <tr>
    <td>
      <div class="muted" style="text-transform:uppercase; font-size:10px; letter-spacing:.05em;">Bill To</div>
      <strong><?= esc($inv['customer_name']) ?></strong><br>
      <span class="muted"><?= esc($inv['customer_mobile']) ?></span>
      <?php if (!empty($inv['customer_email'])): ?><br><span class="muted"><?= esc($inv['customer_email']) ?></span><?php endif; ?>
    </td>
    <td style="text-align:right;">
      <?php if ($inv['staff_name']): ?>
        <div class="muted" style="text-transform:uppercase; font-size:10px; letter-spacing:.05em;">Stylist</div>
        <strong><?= esc($inv['staff_name']) ?></strong>
      <?php endif; ?>
    </td>
  </tr>
</table>

<table class="items" style="margin-top:22px;">
  <thead>
    <tr>
      <th>Item</th>
      <th class="num">Qty</th>
      <th class="num">Unit (<?= esc($currency) ?>)</th>
      <th class="num">Tax %</th>
      <th class="num">Total (<?= esc($currency) ?>)</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= esc($it['name']) ?></td>
        <td class="num"><?= rtrim(rtrim((string)$it['qty'], '0'), '.') ?></td>
        <td class="num"><?= number_format((float)$it['unit_price'], 2) ?></td>
        <td class="num"><?= number_format((float)$it['tax_pct'], 2) ?></td>
        <td class="num"><?= number_format((float)$it['line_total'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<table class="totals" style="float:right; width:280px;">
  <tr><td class="muted">Subtotal</td><td class="num"><?= esc($currency) ?> <?= number_format((float)$inv['subtotal'], 2) ?></td></tr>
  <tr><td class="muted">Discount</td><td class="num">– <?= esc($currency) ?> <?= number_format((float)$inv['discount'], 2) ?></td></tr>
  <tr><td class="muted">Tax</td><td class="num"><?= esc($currency) ?> <?= number_format((float)$inv['tax'], 2) ?></td></tr>
  <tr class="total"><td>Total</td><td class="num"><?= esc($currency) ?> <?= number_format((float)$inv['total'], 2) ?></td></tr>
  <tr><td style="color:#059669;">Paid</td><td class="num" style="color:#059669;"><?= esc($currency) ?> <?= number_format((float)$inv['paid'], 2) ?></td></tr>
  <tr><td style="font-weight:bold; color:<?= $inv['balance']>0?'#dc2626':'#059669' ?>;">Balance</td><td class="num" style="font-weight:bold; color:<?= $inv['balance']>0?'#dc2626':'#059669' ?>;"><?= esc($currency) ?> <?= number_format((float)$inv['balance'], 2) ?></td></tr>
</table>

<div style="clear:both;"></div>

<?php if (!empty($inv['notes'])): ?>
  <div style="margin-top:30px; padding:12px; background:#f9fafb; border-left:3px solid #db2777;">
    <strong>Notes:</strong> <?= esc($inv['notes']) ?>
  </div>
<?php endif; ?>

<div class="footer">
  Thank you for choosing <?= esc($salonName) ?>!<br>
  This is a computer-generated invoice and does not require a signature.
</div>

</body>
</html>
