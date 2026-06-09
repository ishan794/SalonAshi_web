<?php

namespace App\Modules\Billing\Controllers;

use App\Controllers\BaseController;
use App\Modules\Billing\Models\InvoiceModel;
use App\Modules\Appointments\Models\AppointmentModel;
use App\Modules\Customers\Models\CustomerModel;
use App\Modules\Services\Models\ServiceModel;

class InvoicesController extends BaseController
{
    private InvoiceModel $invoices;

    public function __construct()
    {
        $this->invoices = new InvoiceModel();
    }

    public function index()
    {
        $filters = [
            'status' => $this->request->getGet('status'),
            'q'      => trim((string) $this->request->getGet('q')),
            'from'   => $this->request->getGet('from'),
            'to'     => $this->request->getGet('to'),
        ];
        // Sanity-check date format
        foreach (['from','to'] as $k) {
            if ($filters[$k] && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$k])) $filters[$k] = null;
        }

        $perPage = max(5, min(100, (int) ($this->request->getGet('per_page') ?: 20)));
        $pageNum = max(1, (int) $this->request->getGet('page'));
        $page    = $this->invoices->searchPaginated($filters, $perPage, $pageNum);

        return view('layout/admin', [
            'title'   => 'Invoices',
            'content' => view('App\Modules\Billing\Views\invoices_index', [
                'rows'    => $page['rows'],
                'totals'  => $this->invoices->searchTotals($filters),
                'filters' => $filters,
                'page'    => $page,
            ]),
        ]);
    }

    public function create()
    {
        $customers = (new CustomerModel())->orderBy('full_name')->findAll();
        $services  = (new ServiceModel())->where('is_active', 1)->orderBy('name')->findAll();
        $staff     = (new \App\Modules\Staff\Models\StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll();
        return view('layout/admin', [
            'title'   => 'New Invoice',
            'content' => view('App\Modules\Billing\Views\invoice_form', [
                'customers' => $customers, 'services' => $services, 'staff' => $staff, 'preset' => null,
            ]),
        ]);
    }

    public function createFromAppointment(int $appointmentId)
    {
        $appt = (new AppointmentModel())->withDetail($appointmentId);
        if (! $appt) return redirect()->to('/admin/appointments')->with('flash_error','Appointment not found.');

        $invoiceNo = 'INV-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $invoiceId = $this->invoices->insert([
            'invoice_no'     => $invoiceNo,
            'appointment_id' => $appt['id'],
            'customer_id'    => $appt['customer_id'],
            'staff_id'       => $appt['staff_id'],
            'branch_id'      => $appt['branch_id'],
            'subtotal'       => 0,'discount' => 0,'tax' => 0,'total' => 0,
            'paid' => 0,'balance' => 0,'status' => 'unpaid',
            'created_by'     => session('user.id'),
        ], true);

        $db = db_connect();
        foreach ($appt['services'] as $s) {
            $line = (float)$s['price'];
            $db->table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'item_type'  => 'service',
                'ref_id'     => $s['service_id'],
                'name'       => $s['service_name'],
                'qty'        => 1,
                'unit_price' => $line,
                'tax_pct'    => 0,
                'line_total' => $line,
            ]);
        }
        $this->invoices->recalcTotals($invoiceId);
        return redirect()->to('/admin/billing/invoices/' . $invoiceId)->with('flash_success', 'Invoice generated.');
    }

    public function store()
    {
        if (! $this->validate(['customer_id' => 'required|integer', 'items' => 'required'])) {
            return redirect()->back()->withInput()->with('flash_error', 'Customer and at least one line are required.');
        }
        $items = $this->request->getPost('items') ?? [];
        $items = array_values(array_filter($items, fn($r) => !empty($r['name']) && isset($r['unit_price'])));
        if (! $items) return redirect()->back()->withInput()->with('flash_error', 'Add at least one item.');

        $invoiceNo = 'INV-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $invoiceId = $this->invoices->insert([
            'invoice_no'  => $invoiceNo,
            'customer_id' => (int) $this->request->getPost('customer_id'),
            'staff_id'    => ((int) $this->request->getPost('staff_id')) ?: null,
            'branch_id'   => session('user.branch_id') ?: 1,
            'discount'    => (float) $this->request->getPost('discount') ?: 0,
            'notes'       => $this->request->getPost('notes'),
            'subtotal' => 0,'tax' => 0,'total' => 0,'paid' => 0,'balance' => 0,
            'status'      => 'unpaid',
            'created_by'  => session('user.id'),
        ], true);

        $db = db_connect();
        foreach ($items as $it) {
            $qty = (float)($it['qty'] ?? 1);
            $up  = (float)($it['unit_price'] ?? 0);
            $db->table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'item_type'  => $it['item_type'] ?? 'service',
                'name'       => $it['name'],
                'qty'        => $qty,
                'unit_price' => $up,
                'tax_pct'    => (float)($it['tax_pct'] ?? 0),
                'line_total' => $qty * $up,
            ]);
        }
        $this->invoices->recalcTotals($invoiceId);
        return redirect()->to('/admin/billing/invoices/' . $invoiceId)->with('flash_success', 'Invoice created.');
    }

    public function show(int $id)
    {
        $inv = $this->invoices->withCustomer($id);
        if (! $inv) return redirect()->to('/admin/billing/invoices');
        $loyalty = new \App\Modules\Customers\Services\LoyaltyService();
        $staff = (new \App\Modules\Staff\Models\StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll();
        $appointments = (new AppointmentModel())->forCustomer((int) $inv['customer_id']);
        return view('layout/admin', [
            'title'   => 'Invoice ' . $inv['invoice_no'],
            'content' => view('App\Modules\Billing\Views\invoice_show', [
                'inv'             => $inv,
                'items'           => $this->invoices->items($id),
                'payments'        => $this->invoices->payments($id),
                'staff'           => $staff,
                'appointments'    => $appointments,
                'loyaltyEnabled'  => $loyalty->isEnabled(),
                'customerPoints'  => $loyalty->balance((int)$inv['customer_id']),
                'redeemValue'     => $loyalty->redeemValue(),
                'minRedeem'       => $loyalty->minRedeem(),
            ]),
        ]);
    }

    /** Set/clear the stylist and linked appointment on an existing invoice. */
    public function updateAttribution(int $id)
    {
        $inv = $this->invoices->find($id);
        if (! $inv) return redirect()->to('/admin/billing/invoices');

        $staffId = (int) $this->request->getPost('staff_id');
        $apptId  = (int) $this->request->getPost('appointment_id');

        // Validate a chosen appointment belongs to this invoice's customer.
        if ($apptId) {
            $appt = (new AppointmentModel())->find($apptId);
            if (! $appt || (int) $appt['customer_id'] !== (int) $inv['customer_id']) {
                return redirect()->to('/admin/billing/invoices/' . $id)
                    ->with('flash_error', 'That appointment does not belong to this customer.');
            }
            // Inherit the appointment's stylist when none was explicitly picked.
            if (! $staffId && ! empty($appt['staff_id'])) $staffId = (int) $appt['staff_id'];
        }

        $this->invoices->update($id, [
            'staff_id'       => $staffId ?: null,
            'appointment_id' => $apptId ?: null,
        ]);

        helper('system');
        log_action('invoice.attribution', [
            'entity_type' => 'invoice',
            'entity_id'   => $id,
            'description' => 'Updated stylist/appointment on ' . ($inv['invoice_no'] ?? ('#' . $id)),
        ]);

        return redirect()->to('/admin/billing/invoices/' . $id)
            ->with('flash_success', 'Stylist & appointment updated.');
    }

    public function recordPayment(int $id)
    {
        if (! $this->validate(['amount' => 'required|decimal', 'method' => 'required'])) {
            return redirect()->back()->with('flash_error', 'Amount and method required.');
        }
        $amount = (float) $this->request->getPost('amount');

        // Optional payment receipt (e.g. bank-transfer slip).
        $receiptName = null;
        $receipt = $this->request->getFile('receipt');
        if ($receipt && $receipt->isValid() && ! $receipt->hasMoved()) {
            $ext = strtolower($receipt->getExtension() ?: pathinfo($receipt->getClientName(), PATHINFO_EXTENSION));
            if (! in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'], true)) {
                return redirect()->back()->with('flash_error', 'Receipt must be an image (jpg/png/webp/gif) or PDF.');
            }
            if ($receipt->getSize() > 5 * 1024 * 1024) {
                return redirect()->back()->with('flash_error', 'Receipt must be under 5 MB.');
            }
            $receiptName = 'receipt_' . $id . '_' . time() . '.' . $ext;
            $receipt->move(FCPATH . 'uploads', $receiptName, true);
        }

        db_connect()->table('payments')->insert([
            'invoice_id'   => $id,
            'method'       => $this->request->getPost('method'),
            'amount'       => $amount,
            'txn_ref'      => $this->request->getPost('txn_ref'),
            'receipt_path' => $receiptName,
            'status'       => 'success',
            'received_by'  => session('user.id'),
            'paid_at'      => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $this->invoices->recalcTotals($id);

        // Auto-earn loyalty points if invoice is now fully paid
        $msg = 'Payment recorded.';
        $inv = $this->invoices->find($id);
        if ($inv && (float)$inv['balance'] <= 0 && (float)$inv['total'] > 0) {
            $earned = (new \App\Modules\Customers\Services\LoyaltyService())
                ->earn((int)$inv['customer_id'], $id, (float)$inv['total']);
            if ($earned > 0) $msg .= " Customer earned $earned loyalty points.";
        }

        helper('system');
        $invCode = $inv['code'] ?? $inv['invoice_no'] ?? ('#' . $id);
        log_action('invoice.payment', [
            'entity_type' => 'invoice',
            'entity_id'   => $id,
            'description' => 'LKR ' . number_format($amount, 2) . ' via ' . $this->request->getPost('method') . ' on ' . $invCode,
        ]);
        notify_broadcast([
            'type'  => 'payment',
            'title' => 'Payment received · LKR ' . number_format($amount, 2),
            'body'  => $invCode . ' · ' . $this->request->getPost('method'),
            'link'  => site_url('admin/billing/invoices/' . $id),
            'icon'  => 'banknote',
            'color' => 'green',
        ]);
        return redirect()->to('/admin/billing/invoices/' . $id)->with('flash_success', $msg);
    }

    /** Redeem loyalty points → adds discount to invoice */
    public function redeem(int $id)
    {
        $points = (int) $this->request->getPost('points');
        $result = (new \App\Modules\Customers\Services\LoyaltyService())->redeem($id, $points);
        return redirect()->to('/admin/billing/invoices/' . $id)
            ->with($result['ok'] ? 'flash_success' : 'flash_error', $result['msg']);
    }

    public function print(int $id)
    {
        $inv = $this->invoices->withCustomer($id);
        if (! $inv) return redirect()->to('/admin/billing/invoices');
        return view('App\Modules\Billing\Views\invoice_print', [
            'inv' => $inv,
            'items' => $this->invoices->items($id),
            'payments' => $this->invoices->payments($id),
        ]);
    }

    public function pdf(int $id)
    {
        $inv = $this->invoices->withCustomer($id);
        if (! $inv) return redirect()->to('/admin/billing/invoices');

        $html = view('App\Modules\Billing\Views\invoice_pdf', [
            'inv'      => $inv,
            'items'    => $this->invoices->items($id),
            'payments' => $this->invoices->payments($id),
            's'        => new \App\Modules\Settings\Models\SettingModel(),
        ]);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $inv['invoice_no'] . '.pdf"')
            ->setBody($dompdf->output());
    }

    public function email(int $id)
    {
        $inv = $this->invoices->withCustomer($id);
        if (! $inv) return redirect()->to('/admin/billing/invoices');

        $to = trim((string) $this->request->getPost('to')) ?: ($inv['customer_email'] ?? null);
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('/admin/billing/invoices/' . $id)
                ->with('flash_error', 'Need a valid recipient email — set one on the customer profile or pass it in.');
        }

        $s   = new \App\Modules\Settings\Models\SettingModel();
        $cfg = $s->group('smtp_');
        if (empty($cfg['host']) || empty($cfg['from_email'])) {
            return redirect()->to('/admin/billing/invoices/' . $id)
                ->with('flash_error', 'Configure SMTP first under Settings → SMTP.');
        }

        // Build the PDF and attach it
        $pdfHtml = view('App\Modules\Billing\Views\invoice_pdf', [
            'inv'      => $inv,
            'items'    => $this->invoices->items($id),
            'payments' => $this->invoices->payments($id),
            's'        => $s,
        ]);
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($pdfHtml);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfFile = WRITEPATH . 'cache/invoice-' . $inv['invoice_no'] . '.pdf';
        @file_put_contents($pdfFile, $dompdf->output());

        $salonName = $s->get('salon_name', 'SalonCMS');
        $body = '<p>Hi ' . esc($inv['customer_name']) . ',</p>'
              . '<p>Please find attached invoice <strong>' . esc($inv['invoice_no']) . '</strong> for <strong>'
              . esc($s->get('salon_currency', 'LKR')) . ' ' . number_format((float)$inv['total'], 2) . '</strong>.</p>'
              . '<p>Thanks for choosing us!<br>— ' . esc($salonName) . '</p>';

        $email = service('email');
        $email->initialize([
            'protocol'  => 'smtp',
            'SMTPHost'  => $cfg['host'],
            'SMTPPort'  => (int) ($cfg['port'] ?? 587),
            'SMTPUser'  => $cfg['user'] ?? '',
            'SMTPPass'  => $cfg['pass'] ?? '',
            'SMTPCrypto'=> $cfg['encryption'] ?? '',
            'mailType'  => 'html',
            'charset'   => 'utf-8',
            'wordWrap'  => true,
        ]);
        $email->setFrom($cfg['from_email'], $cfg['from_name'] ?? $salonName);
        $email->setTo($to);
        $email->setSubject('Invoice ' . $inv['invoice_no'] . ' from ' . $salonName);
        $email->setMessage($body);
        $email->attach($pdfFile);

        if ($email->send()) {
            @unlink($pdfFile);
            return redirect()->to('/admin/billing/invoices/' . $id)
                ->with('flash_success', 'Invoice emailed to ' . esc($to));
        }
        @unlink($pdfFile);
        return redirect()->to('/admin/billing/invoices/' . $id)
            ->with('flash_error', 'Send failed — check SMTP settings.');
    }
}
