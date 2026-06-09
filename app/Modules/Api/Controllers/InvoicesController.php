<?php

namespace App\Modules\Api\Controllers;

use App\Modules\Billing\Models\InvoiceModel;

class InvoicesController extends ApiBaseController
{
    /**
     * GET /api/invoices?status=&from=&to=&q=&page=&per_page=
     * Stylists don't have billing access — 403.
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist()) return $this->forbidden('Stylists cannot access billing.');
        if ($r = $this->requirePerm('invoices.view')) return $r;

        $filters = [
            'status' => $this->request->getGet('status'),
            'q'      => trim((string) ($this->request->getGet('q') ?: '')),
            'from'   => $this->request->getGet('from'),
            'to'     => $this->request->getGet('to'),
        ];
        foreach (['from','to'] as $k) {
            if ($filters[$k] && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$k])) $filters[$k] = null;
        }
        $perPage = max(5, min(100, (int) ($this->request->getGet('per_page') ?: 20)));
        $page    = max(1, (int) ($this->request->getGet('page') ?: 1));

        $model  = new InvoiceModel();
        $result = $model->searchPaginated($filters, $perPage, $page);

        return $this->ok($result['rows'], $this->pageMeta($result));
    }

    /**
     * POST /api/invoices
     * Body: { customer_id, staff_id?, items:[{name,qty,unit_price,tax_pct}], discount?, notes?, appointment_id? }
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist()) return $this->forbidden('Stylists cannot create invoices.');
        if ($r = $this->requirePerm('invoices.create')) return $r;

        $body = $this->body();
        $customerId = (int) ($body['customer_id'] ?? 0);
        $items = array_values(array_filter((array) ($body['items'] ?? []),
            fn($i) => ! empty($i['name']) && isset($i['unit_price'])
        ));

        if (! $customerId) return $this->fail('customer_id is required.', 400);
        if (! $items)      return $this->fail('At least one line item is required.', 400);

        $invoiceNo = 'INV-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $model     = new InvoiceModel();
        $invoiceId = $model->insert([
            'invoice_no'     => $invoiceNo,
            'customer_id'    => $customerId,
            'staff_id'       => ((int) ($body['staff_id'] ?? 0)) ?: null,
            'appointment_id' => ((int) ($body['appointment_id'] ?? 0)) ?: null,
            'branch_id'      => $this->apiUser->branchId() ?? 1,
            'discount'       => (float) ($body['discount'] ?? 0),
            'notes'          => $body['notes'] ?? null,
            'subtotal' => 0, 'tax' => 0, 'total' => 0, 'paid' => 0, 'balance' => 0,
            'status'         => 'unpaid',
            'created_by'     => $this->apiUser->id(),
        ], true);

        $db = db_connect();
        foreach ($items as $it) {
            $qty = (float) ($it['qty'] ?? 1);
            $up  = (float) ($it['unit_price'] ?? 0);
            $db->table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'item_type'  => $it['item_type'] ?? 'service',
                'name'       => $it['name'],
                'qty'        => $qty,
                'unit_price' => $up,
                'tax_pct'    => (float) ($it['tax_pct'] ?? 0),
                'line_total' => $qty * $up,
            ]);
        }
        $model->recalcTotals($invoiceId);

        $inv = $model->withCustomer($invoiceId);
        return $this->ok($inv, [], 201);
    }

    /**
     * GET /api/invoices/{id}
     */
    public function show(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist()) return $this->forbidden('Stylists cannot access billing.');

        $model = new InvoiceModel();
        $inv   = $model->withCustomer($id);
        if (! $inv) return $this->notFound('Invoice not found.');

        return $this->ok([
            'invoice'  => $inv,
            'items'    => $model->items($id),
            'payments' => $model->payments($id),
        ]);
    }

    /**
     * POST /api/invoices/{id}/payments
     * Body: { method, amount, txn_ref? }
     */
    public function recordPayment(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist()) return $this->forbidden();
        if ($r = $this->requirePerm('payments.record')) return $r;

        $body   = $this->body();
        $amount = (float) ($body['amount'] ?? 0);
        $method = trim((string) ($body['method'] ?? ''));

        if ($amount <= 0) return $this->fail('amount must be greater than zero.', 400);
        if (! $method)   return $this->fail('method is required.', 400);

        $model = new InvoiceModel();
        $inv   = $model->find($id);
        if (! $inv) return $this->notFound('Invoice not found.');

        db_connect()->table('payments')->insert([
            'invoice_id'  => $id,
            'method'      => $method,
            'amount'      => $amount,
            'txn_ref'     => $body['txn_ref'] ?? null,
            'status'      => 'success',
            'received_by' => $this->apiUser->id(),
            'paid_at'     => date('Y-m-d H:i:s'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $model->recalcTotals($id);

        // Payment update notification (in-app + push).
        helper('system');
        $fresh = $model->withCustomer($id);
        $cur = (new \App\Modules\Settings\Models\SettingModel())->get('salon_currency', 'LKR');
        notify_broadcast([
            'type'  => 'payment',
            'title' => 'Payment received · ' . $cur . ' ' . number_format($amount, 2),
            'body'  => ($fresh['invoice_no'] ?? '#' . $id) . ' · ' . str_replace('_', ' ', $method)
                       . (($fresh['balance'] ?? 0) > 0 ? ' · bal ' . $cur . ' ' . number_format((float) $fresh['balance'], 2) : ' · paid'),
            'icon'  => 'banknote',
            'color' => 'green',
            'link'  => '/invoices/' . $id,
        ]);

        return $this->ok($fresh);
    }

    /**
     * POST /api/invoices/{id}/attribution
     * Body: { staff_id, appointment_id? }
     */
    public function updateAttribution(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist()) return $this->forbidden();

        $body    = $this->body();
        $model   = new InvoiceModel();
        $inv     = $model->find($id);
        if (! $inv) return $this->notFound('Invoice not found.');

        $staffId = ((int) ($body['staff_id'] ?? 0)) ?: null;
        $apptId  = ((int) ($body['appointment_id'] ?? 0)) ?: null;

        $model->update($id, ['staff_id' => $staffId, 'appointment_id' => $apptId]);
        return $this->ok(['id' => $id, 'staff_id' => $staffId, 'appointment_id' => $apptId]);
    }

    /**
     * PATCH /api/invoices/{id}/status
     * Body: { status: draft|unpaid|partial|paid|refunded|cancelled }
     */
    public function setStatus(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist()) return $this->forbidden();
        if ($r = $this->requirePerm('invoices.create')) return $r;

        $allowed = ['draft', 'unpaid', 'partial', 'paid', 'refunded', 'cancelled'];
        $status  = trim((string) ($this->body()['status'] ?? ''));
        if (! in_array($status, $allowed, true)) {
            return $this->fail('Invalid status. Allowed: ' . implode(', ', $allowed), 400);
        }
        $model = new InvoiceModel();
        if (! $model->find($id)) return $this->notFound('Invoice not found.');
        $model->update($id, ['status' => $status]);
        return $this->ok($model->withCustomer($id));
    }

    /**
     * GET /api/invoices/{id}/pdf — returns the invoice as a PDF (token-authed).
     */
    public function pdf(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist()) return $this->forbidden();
        $model = new InvoiceModel();
        $inv = $model->withCustomer($id);
        if (! $inv) return $this->notFound('Invoice not found.');

        $html = view('App\Modules\Billing\Views\invoice_pdf', [
            'inv'      => $inv,
            'items'    => $model->items($id),
            'payments' => $model->payments($id),
            's'        => new \App\Modules\Settings\Models\SettingModel(),
        ]);
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $inv['invoice_no'] . '.pdf"')
            ->setBody($dompdf->output());
    }

    /**
     * POST /api/invoices/{id}/email
     * Body: { to?: email } — defaults to the customer's email.
     */
    public function email(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->apiUser->isStylist()) return $this->forbidden();
        $model = new InvoiceModel();
        $inv = $model->withCustomer($id);
        if (! $inv) return $this->notFound('Invoice not found.');

        $to = trim((string) ($this->body()['to'] ?? '')) ?: ($inv['customer_email'] ?? '');
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('A valid recipient email is required (none on customer profile).', 422);
        }

        $s   = new \App\Modules\Settings\Models\SettingModel();
        $cfg = $s->group('smtp_');
        if (empty($cfg['host']) || empty($cfg['from_email'])) {
            return $this->fail('SMTP is not configured. Set it under Settings → SMTP on the web.', 422);
        }

        $pdfHtml = view('App\Modules\Billing\Views\invoice_pdf', [
            'inv' => $inv, 'items' => $model->items($id), 'payments' => $model->payments($id), 's' => $s,
        ]);
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($pdfHtml);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfFile = WRITEPATH . 'cache/invoice-' . $inv['invoice_no'] . '.pdf';
        @file_put_contents($pdfFile, $dompdf->output());

        $salon = $s->get('salon_name', 'SalonCMS');
        $cur   = $s->get('salon_currency', 'LKR');
        $body  = '<p>Hi ' . esc($inv['customer_name']) . ',</p>'
               . '<p>Please find attached invoice <strong>' . esc($inv['invoice_no']) . '</strong> for <strong>'
               . esc($cur) . ' ' . number_format((float) $inv['total'], 2) . '</strong>.</p>'
               . '<p>Thank you!<br>— ' . esc($salon) . '</p>';

        $email = service('email');
        $email->initialize([
            'protocol' => 'smtp', 'SMTPHost' => $cfg['host'], 'SMTPPort' => (int) ($cfg['port'] ?? 587),
            'SMTPUser' => $cfg['user'] ?? '', 'SMTPPass' => $cfg['pass'] ?? '',
            'SMTPCrypto' => $cfg['encryption'] ?? '', 'mailType' => 'html', 'charset' => 'utf-8', 'wordWrap' => true,
        ]);
        $email->setFrom($cfg['from_email'], $cfg['from_name'] ?? $salon);
        $email->setTo($to);
        $email->setSubject('Invoice ' . $inv['invoice_no'] . ' from ' . $salon);
        $email->setMessage($body);
        $email->attach($pdfFile);

        $ok = $email->send();
        @unlink($pdfFile);

        if (! $ok) return $this->fail('Email send failed — check SMTP settings.', 502);

        helper('system');
        log_action('invoice.email', ['entity_type' => 'invoice', 'entity_id' => $id, 'description' => 'Emailed ' . $inv['invoice_no'] . ' to ' . $to]);
        return $this->ok(['sent_to' => $to]);
    }
}
