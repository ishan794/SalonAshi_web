<?php

namespace App\Modules\Api\Controllers;

use App\Modules\Customers\Models\CustomerModel;

class CustomersController extends ApiBaseController
{
    /**
     * GET /api/customers?q=&page=&per_page=
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('customers.view')) return $r;

        $q       = trim((string) ($this->request->getGet('q') ?: ''));
        $perPage = max(5, min(100, (int) ($this->request->getGet('per_page') ?: 20)));
        $page    = max(1, (int) ($this->request->getGet('page') ?: 1));

        $model  = new CustomerModel();
        $result = $model->paginatedSearch($q !== '' ? $q : null, $perPage, $page);

        return $this->ok($result['rows'], $this->pageMeta($result));
    }

    /**
     * POST /api/customers
     * Body: { full_name, mobile, email?, gender?, birthday?, address? }
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('customers.create')) return $r;
        $body = $this->body();

        $name   = trim((string) ($body['full_name'] ?? ''));
        $mobile = trim((string) ($body['mobile']    ?? ''));
        if (! $name) return $this->fail('full_name is required.', 400);

        $model = new CustomerModel();
        $id = $model->insert([
            'full_name' => $name,
            'mobile'    => $mobile ?: null,
            'email'     => trim((string) ($body['email']    ?? '')) ?: null,
            'gender'    => $body['gender']   ?? null,
            'birthday'  => $body['birthday'] ?? null,
            'address'   => trim((string) ($body['address'] ?? '')) ?: null,
            'status'    => 'active',
        ], true);

        return $this->ok(['id' => $id, 'full_name' => $name], [], 201);
    }

    /**
     * GET /api/customers/{id}
     */
    public function show(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('customers.view')) return $r;

        $model    = new CustomerModel();
        $customer = $model->find($id);
        if (! $customer) return $this->notFound('Customer not found.');

        $db = db_connect();

        // Recent appointments
        $appointments = $db->table('appointments a')
            ->select('a.id, a.code, a.start_at, a.end_at, a.status, s.full_name AS staff_name')
            ->join('staff s', 's.id = a.staff_id', 'left')
            ->where('a.customer_id', $id)
            ->orderBy('a.start_at', 'desc')
            ->limit(10)
            ->get()->getResultArray();

        // Recent invoices
        $invoices = $db->table('invoices')
            ->select('id, invoice_no, total, paid, balance, status, created_at')
            ->where('customer_id', $id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()->getResultArray();

        // Notes (pinned first)
        $notes = $db->table('customer_notes')
            ->where('customer_id', $id)
            ->orderBy('is_pinned', 'desc')->orderBy('id', 'desc')
            ->limit(50)->get()->getResultArray();

        // Service / treatment history
        $history = $db->table('customer_service_history')
            ->where('customer_id', $id)
            ->orderBy('service_date', 'desc')
            ->limit(50)->get()->getResultArray();

        // Files / photos
        $files = $db->table('customer_files')
            ->where('customer_id', $id)
            ->orderBy('id', 'desc')
            ->limit(50)->get()->getResultArray();
        $base = rtrim(base_url(), '/');
        foreach ($files as &$f) {
            $f['url'] = $base . '/uploads/' . $f['file_path'];
            $f['is_image'] = (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', $f['file_path']);
        }
        unset($f);

        // Allergies & preferences (best-effort)
        $allergies   = $db->table('customer_allergies')->where('customer_id', $id)->get()->getResultArray();

        return $this->ok([
            'customer'     => $customer,
            'appointments' => $appointments,
            'invoices'     => $invoices,
            'notes'        => $notes,
            'history'      => $history,
            'files'        => $files,
            'allergies'    => $allergies,
        ]);
    }

    /**
     * POST /api/customers/{id}/notes
     * Body: { body, title?, note_type?, is_pinned? }
     */
    public function addNote(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('customers.edit')) return $r;
        $body = $this->body();
        $text = trim((string) ($body['body'] ?? ''));
        if (! $text) return $this->fail('Note body is required.', 400);

        $db = db_connect();
        if (! $db->table('customers')->where('id', $id)->countAllResults()) {
            return $this->notFound('Customer not found.');
        }
        $db->table('customer_notes')->insert([
            'customer_id' => $id,
            'staff_id'    => $this->apiUser->staffId(),
            'staff_name'  => $this->apiUser->get('name'),
            'note_type'   => $body['note_type'] ?? 'general',
            'title'       => trim((string) ($body['title'] ?? '')) ?: null,
            'body'        => $text,
            'is_pinned'   => ! empty($body['is_pinned']) ? 1 : 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        return $this->ok(['id' => $db->insertID()], [], 201);
    }

    /**
     * POST /api/customers/{id}/files  (multipart: file=..., label?=...)
     */
    public function uploadFile(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('customers.edit')) return $r;

        $db = db_connect();
        if (! $db->table('customers')->where('id', $id)->countAllResults()) {
            return $this->notFound('Customer not found.');
        }

        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $this->fail('Pick a valid file to upload.', 400);
        }
        $ext = strtolower($file->getExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg','jpeg','png','gif','webp','pdf','heic'], true)) {
            return $this->fail('Allowed: images, PDF.', 422);
        }
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->fail('File must be under 10 MB.', 422);
        }
        $name = 'cust_' . $id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]+/', '-', $file->getClientName());
        $file->move(FCPATH . 'uploads', $name, true);

        $db->table('customer_files')->insert([
            'customer_id' => $id,
            'file_name'   => $file->getClientName(),
            'file_path'   => $name,
            'mime_type'   => $file->getClientMimeType(),
            'size_bytes'  => $file->getSize(),
            'label'       => trim((string) $this->request->getPost('label')) ?: null,
            'uploaded_by' => $this->apiUser->id(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->ok([
            'id'   => $db->insertID(),
            'url'  => rtrim(base_url(), '/') . '/uploads/' . $name,
            'name' => $file->getClientName(),
        ], [], 201);
    }
}
