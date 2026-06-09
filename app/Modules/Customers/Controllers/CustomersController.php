<?php

namespace App\Modules\Customers\Controllers;

use App\Controllers\BaseController;
use App\Modules\Customers\Models\CustomerModel;

class CustomersController extends BaseController
{
    private CustomerModel $customers;

    public function __construct()
    {
        $this->customers = new CustomerModel();
    }

    public function index()
    {
        $q       = $this->request->getGet('q');
        $pageNum = max(1, (int) $this->request->getGet('page'));
        $perPage = max(5, min(100, (int) ($this->request->getGet('per_page') ?: 20)));

        $page = $this->customers->paginatedSearch($q, $perPage, $pageNum);

        // Membership distribution for the summary cards (whole DB, not just this page)
        $stats = db_connect()->table('customers')
            ->select("COUNT(*) total,
                      SUM(membership='gold') gold,
                      SUM(membership='silver') silver,
                      SUM(membership='platinum') platinum,
                      SUM(membership IS NULL OR membership='' OR membership='none') none_count,
                      SUM(status='blocked') blocked")
            ->get()->getRowArray();

        return view('layout/admin', [
            'title'   => 'Customers',
            'content' => view('App\Modules\Customers\Views\index', [
                'rows'        => $page['rows'],
                'q'           => $q,
                'stats'       => $stats,
                'currentPage' => $page['currentPage'],
                'totalPages'  => $page['totalPages'],
                'totalCount'  => $page['total'],
                'perPage'     => $page['perPage'],
                'firstIndex'  => $page['firstIndex'],
                'lastIndex'   => $page['lastIndex'],
            ]),
        ]);
    }

    /** JSON autocomplete for the live search dropdown. */
    public function suggest()
    {
        $q = trim((string) $this->request->getGet('q'));
        if (mb_strlen($q) < 2) return $this->response->setJSON([]);
        $rows = $this->customers->search($q, 8);
        $out = array_map(fn($c) => [
            'id'     => (int) $c['id'],
            'name'   => $c['full_name'],
            'mobile' => phone_local($c['mobile'] ?? ''),
            'email'  => $c['email'] ?? '',
        ], $rows);
        return $this->response->setJSON($out);
    }

    public function create()
    {
        return view('layout/admin', [
            'title'   => 'New Customer',
            'content' => view('App\Modules\Customers\Views\form', ['row' => null]),
        ]);
    }

    public function store()
    {
        $data = $this->validatedInput();
        if (! $data) return redirect()->back()->withInput();

        $this->customers->insert($data);
        return redirect()->to('/admin/customers')->with('flash_success', 'Customer added.');
    }

    public function edit(int $id)
    {
        $row = $this->customers->find($id);
        if (! $row) return redirect()->to('/admin/customers')->with('flash_error', 'Customer not found.');
        return view('layout/admin', [
            'title'   => 'Edit Customer',
            'content' => view('App\Modules\Customers\Views\form', ['row' => $row]),
        ]);
    }

    public function update(int $id)
    {
        $data = $this->validatedInput();
        if (! $data) return redirect()->back()->withInput();

        $this->customers->update($id, $data);
        return redirect()->to('/admin/customers')->with('flash_success', 'Customer updated.');
    }

    public function show(int $id)
    {
        $row = $this->customers->find($id);
        if (! $row) return redirect()->to('/admin/customers')->with('flash_error', 'Customer not found.');

        $appts = db_connect()->table('appointments a')
            ->select('a.*')
            ->where('customer_id', $id)
            ->orderBy('start_at', 'desc')
            ->limit(20)->get()->getResultArray();

        $invoices = db_connect()->table('invoices')
            ->where('customer_id', $id)
            ->orderBy('id', 'desc')
            ->limit(20)->get()->getResultArray();

        $reliability = (new \App\Modules\Appointments\Models\CancellationModel())->reliabilityFor($id);
        $cancellations = db_connect()->table('appointment_cancellations')
            ->where('customer_id', $id)
            ->orderBy('cancelled_at','desc')
            ->limit(10)->get()->getResultArray();

        $loyalty = new \App\Modules\Customers\Services\LoyaltyService();

        // New service-history + records data
        $history     = (new \App\Modules\Customers\Models\CustomerHistoryModel())->forCustomer($id, 100);
        $notes       = (new \App\Modules\Customers\Models\CustomerNoteModel())->forCustomer($id);
        $allergies   = (new \App\Modules\Customers\Models\CustomerAllergyModel())->forCustomer($id);
        $preferences = (new \App\Modules\Customers\Models\CustomerPreferenceModel())->forCustomer($id);
        $files       = (new \App\Modules\Customers\Models\CustomerFileModel())->forCustomer($id);

        return view('layout/admin', [
            'title'   => 'Customer Profile',
            'content' => view('App\Modules\Customers\Views\show', [
                'row' => $row, 'appts' => $appts, 'invoices' => $invoices,
                'reliability' => $reliability, 'cancellations' => $cancellations,
                'loyaltyEnabled'  => $loyalty->isEnabled(),
                'loyaltyBalance'  => $loyalty->balance($id),
                'loyaltyLifetime' => $loyalty->lifetimeEarned($id),
                'loyaltyTxns'     => $loyalty->recent($id, 10),
                'loyaltyTiers'    => $loyalty->tierThresholds(),
                'history'     => $history,
                'notes'       => $notes,
                'allergies'   => $allergies,
                'preferences' => $preferences,
                'files'       => $files,
            ]),
        ]);
    }

    // ── Notes ──
    public function addNote(int $id)
    {
        $in = $this->request->getPost();
        (new \App\Modules\Customers\Models\CustomerNoteModel())->insert([
            'customer_id' => $id,
            'staff_id'    => session('user.id') ?: null,
            'staff_name'  => session('user.name') ?: null,
            'note_type'   => $in['note_type'] ?? 'general',
            'title'       => trim((string) ($in['title'] ?? '')),
            'body'        => trim((string) ($in['body'] ?? '')),
            'is_pinned'   => ! empty($in['is_pinned']) ? 1 : 0,
        ]);
        return redirect()->to('/admin/customers/' . $id . '#tab-notes')->with('flash_success', 'Note added.');
    }

    public function deleteNote(int $id, int $noteId)
    {
        (new \App\Modules\Customers\Models\CustomerNoteModel())->delete($noteId);
        return redirect()->to('/admin/customers/' . $id . '#tab-notes');
    }

    // ── Allergies ──
    public function addAllergy(int $id)
    {
        $in = $this->request->getPost();
        (new \App\Modules\Customers\Models\CustomerAllergyModel())->insert([
            'customer_id'  => $id,
            'allergy_name' => trim((string) ($in['allergy_name'] ?? '')),
            'severity'     => in_array(($in['severity'] ?? 'mild'), ['mild','moderate','severe'], true) ? $in['severity'] : 'mild',
            'notes'        => trim((string) ($in['notes'] ?? '')),
        ]);
        return redirect()->to('/admin/customers/' . $id . '#tab-allergies')->with('flash_success', 'Allergy added.');
    }

    public function deleteAllergy(int $id, int $allergyId)
    {
        (new \App\Modules\Customers\Models\CustomerAllergyModel())->delete($allergyId);
        return redirect()->to('/admin/customers/' . $id . '#tab-allergies');
    }

    // ── Preferences ──
    public function savePreferences(int $id)
    {
        $pairs = (array) $this->request->getPost('preferences');
        $model = new \App\Modules\Customers\Models\CustomerPreferenceModel();
        foreach ($pairs as $key => $val) {
            $key = trim((string) $key); $val = trim((string) $val);
            if ($key === '') continue;
            $model->setKv($id, $key, $val);
        }
        // Add new pref if user submitted name + value
        $newKey = trim((string) $this->request->getPost('new_pref_key'));
        $newVal = trim((string) $this->request->getPost('new_pref_value'));
        if ($newKey !== '') $model->setKv($id, $newKey, $newVal);
        return redirect()->to('/admin/customers/' . $id . '#tab-preferences')->with('flash_success', 'Preferences saved.');
    }

    public function deletePreference(int $id, int $prefId)
    {
        (new \App\Modules\Customers\Models\CustomerPreferenceModel())->delete($prefId);
        return redirect()->to('/admin/customers/' . $id . '#tab-preferences');
    }

    // ── Service history (add per-visit notes/photos/formula/rating) ──
    public function updateHistory(int $id, int $historyId)
    {
        $in = $this->request->getRawInput();
        $model = new \App\Modules\Customers\Models\CustomerHistoryModel();
        $row = $model->find($historyId);
        if (! $row || (int)$row['customer_id'] !== $id) return redirect()->back();

        $payload = [
            'notes'        => trim((string) ($in['notes'] ?? '')),
            'product_used' => trim((string) ($in['product_used'] ?? '')),
            'formula'      => trim((string) ($in['formula'] ?? '')),
            'rating'       => isset($in['rating']) ? max(0, min(5, (int)$in['rating'])) : null,
        ];

        // Photo uploads — before/after
        foreach (['before_image','after_image'] as $field) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && ! $file->hasMoved()) {
                $name = 'csh_' . $historyId . '_' . $field . '_' . time() . '.' . $file->getExtension();
                $file->move(FCPATH . 'uploads', $name, true);
                $payload[$field] = $name;
            }
        }
        $model->update($historyId, $payload);
        return redirect()->to('/admin/customers/' . $id . '#tab-history')->with('flash_success', 'Treatment record updated.');
    }

    // ── Files ──
    public function uploadFile(int $id)
    {
        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('flash_error', 'Pick a file to upload.');
        }
        $name = 'cust_' . $id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]+/', '-', $file->getClientName());
        $file->move(FCPATH . 'uploads', $name, true);
        (new \App\Modules\Customers\Models\CustomerFileModel())->insert([
            'customer_id' => $id,
            'file_name'   => $file->getClientName(),
            'file_path'   => $name,
            'mime_type'   => $file->getClientMimeType(),
            'size_bytes'  => $file->getSize(),
            'label'       => trim((string) $this->request->getPost('label')),
            'uploaded_by' => session('user.id') ?: null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/customers/' . $id . '#tab-files')->with('flash_success', 'File uploaded.');
    }

    public function deleteFile(int $id, int $fileId)
    {
        $model = new \App\Modules\Customers\Models\CustomerFileModel();
        $row = $model->find($fileId);
        if ($row && (int)$row['customer_id'] === $id) {
            if ($row['file_path'] && is_file(FCPATH . 'uploads/' . $row['file_path'])) @unlink(FCPATH . 'uploads/' . $row['file_path']);
            $model->delete($fileId);
        }
        return redirect()->to('/admin/customers/' . $id . '#tab-files');
    }

    public function toggleBlock(int $id)
    {
        $c = $this->customers->find($id);
        if (! $c) return redirect()->back()->with('flash_error', 'Customer not found.');
        $new = ($c['status'] ?? 'active') === 'blocked' ? 'active' : 'blocked';
        $this->customers->update($id, ['status' => $new]);
        helper('system');
        log_action('customer.' . ($new === 'blocked' ? 'block' : 'unblock'), [
            'entity_type' => 'customer', 'entity_id' => $id,
            'description' => ($new === 'blocked' ? 'Blocked' : 'Unblocked') . ' customer ' . $c['full_name'],
            'severity' => $new === 'blocked' ? 'warning' : 'info',
        ]);
        return redirect()->back()->with('flash_success', 'Customer ' . ($new === 'blocked' ? 'blocked' : 'unblocked') . '.');
    }

    public function destroy(int $id)
    {
        $c = $this->customers->find($id);
        $this->customers->delete($id);
        helper('system');
        log_action('customer.delete', ['entity_type' => 'customer', 'entity_id' => $id, 'description' => 'Deleted customer ' . ($c['full_name'] ?? ('#' . $id)), 'severity' => 'warning']);
        return redirect()->to('/admin/customers')->with('flash_success', 'Customer deleted.');
    }

    private function validatedInput(): ?array
    {
        $rules = [
            'full_name' => 'required|min_length[2]|max_length[150]',
            // Country dial code (digits only, 1–4).
            'dial_code' => 'permit_empty|regex_match[/^[0-9]{1,4}$/]',
            // National number: 7–15 digits (spaces/dashes allowed); country code is separate.
            'mobile'    => 'required|regex_match[/^[0-9\s\-]{7,17}$/]',
            // Require a proper email with a real-looking TLD (blocks foo@local, foo@x).
            'email'     => 'permit_empty|valid_email|regex_match[/^[^@\s]+@[^@\s]+\.[a-zA-Z]{2,}$/]',
            'gender'    => 'permit_empty|in_list[male,female,other]',
            'birthday'  => 'permit_empty|valid_date[Y-m-d]',
            'membership' => 'permit_empty|in_list[none,silver,gold,platinum]',
        ];
        $messages = [
            'mobile' => [
                'regex_match' => 'Please enter a valid phone number (7–15 digits, no country code).',
            ],
            'email' => [
                'regex_match' => 'Please enter a valid email address (e.g. name@example.com).',
            ],
        ];
        if (! $this->validate($rules, $messages)) {
            session()->setFlashdata('flash_error', 'Please fix: ' . implode(' · ', $this->validator->getErrors()));
            return null;
        }
        $in = $this->request->getPost();
        // Combine the selected country code with the national number → "+94771234567".
        $dial   = preg_replace('/\D/', '', (string) ($in['dial_code'] ?? '94')) ?: '94';
        $mobile = phone_compose($dial, $in['mobile']);
        return [
            'branch_id'   => session('user.branch_id') ?: 1,
            'full_name'   => $in['full_name'],
            'mobile'      => $mobile,
            'email'       => ($in['email'] ?? '') ?: null,
            'gender'      => ($in['gender'] ?? '') ?: null,
            'birthday'    => ($in['birthday'] ?? '') ?: null,
            'address'     => $in['address'] ?? null,
            'notes'       => $in['notes'] ?? null,
            'membership'  => ($in['membership'] ?? '') ?: 'none',
        ];
    }
}
