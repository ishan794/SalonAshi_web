<?php

namespace App\Modules\Billing\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table         = 'invoices';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'invoice_no','appointment_id','customer_id','staff_id','branch_id',
        'subtotal','discount','tax','total','paid','balance',
        'status','notes','created_by',
    ];

    public function withCustomer(int $id): ?array
    {
        return $this->db->table('invoices i')
            ->select('i.*, c.full_name AS customer_name, c.mobile AS customer_mobile, c.email AS customer_email, s.full_name AS staff_name')
            ->join('customers c','c.id=i.customer_id','left')
            ->join('staff s','s.id=i.staff_id','left')
            ->where('i.id', $id)
            ->get()->getRowArray();
    }

    public function listAll(?string $status = null): array
    {
        // Backwards-compatible thin wrapper around search()
        return $this->search(['status' => $status]);
    }

    /**
     * Filtered search for the invoices list.
     * @param array $f  status, q (free text — matches invoice_no, customer_name, mobile),
     *                  from (Y-m-d), to (Y-m-d), customer_id
     */
    public function search(array $f = []): array
    {
        $q = $this->db->table('invoices i')
            ->select('i.*, c.full_name AS customer_name, c.mobile AS customer_mobile')
            ->join('customers c','c.id=i.customer_id','left')
            ->orderBy('i.id','desc');

        if (! empty($f['status']))      $q->where('i.status', $f['status']);
        if (! empty($f['customer_id'])) $q->where('i.customer_id', (int)$f['customer_id']);
        if (! empty($f['from']))        $q->where('DATE(i.created_at) >=', $f['from']);
        if (! empty($f['to']))          $q->where('DATE(i.created_at) <=', $f['to']);

        if (! empty($f['q'])) {
            $term = trim((string)$f['q']);
            $q->groupStart()
                ->like('i.invoice_no', $term)
                ->orLike('c.full_name', $term)
                ->orLike('c.mobile', $term)
                ->groupEnd();
        }
        return $q->get()->getResultArray();
    }

    /** Apply the same filters as search() to a builder (shared by paginated count + rows). */
    private function applyFilters($q, array $f)
    {
        if (! empty($f['status']))      $q->where('i.status', $f['status']);
        if (! empty($f['customer_id'])) $q->where('i.customer_id', (int)$f['customer_id']);
        if (! empty($f['from']))        $q->where('DATE(i.created_at) >=', $f['from']);
        if (! empty($f['to']))          $q->where('DATE(i.created_at) <=', $f['to']);
        if (! empty($f['q'])) {
            $term = trim((string)$f['q']);
            $q->groupStart()
                ->like('i.invoice_no', $term)
                ->orLike('c.full_name', $term)
                ->orLike('c.mobile', $term)
                ->groupEnd();
        }
        return $q;
    }

    /** Paginated search → rows + page metadata. */
    public function searchPaginated(array $f, int $perPage = 20, int $page = 1): array
    {
        $perPage = max(5, min(100, $perPage));
        $page    = max(1, $page);

        $countQ = $this->applyFilters($this->db->table('invoices i')->join('customers c','c.id=i.customer_id','left'), $f);
        $total  = (int) $countQ->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;

        $rowsQ = $this->applyFilters(
            $this->db->table('invoices i')
                ->select('i.*, c.full_name AS customer_name, c.mobile AS customer_mobile, s.full_name AS staff_name')
                ->join('customers c','c.id=i.customer_id','left')
                ->join('staff s','s.id=i.staff_id','left')
                ->orderBy('i.id','desc'),
            $f
        );
        $rows = $rowsQ->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return [
            'rows' => $rows, 'total' => $total, 'totalPages' => $totalPages,
            'currentPage' => $page, 'perPage' => $perPage,
            'firstIndex' => $total === 0 ? 0 : (($page - 1) * $perPage + 1),
            'lastIndex'  => min($page * $perPage, $total),
        ];
    }

    /** Aggregate totals for the current filtered set (for the search-results KPI bar) */
    public function searchTotals(array $f = []): array
    {
        $rows = $this->search($f);
        $billed = 0; $paid = 0; $balance = 0;
        foreach ($rows as $r) {
            $billed  += (float)$r['total'];
            $paid    += (float)$r['paid'];
            $balance += (float)$r['balance'];
        }
        return ['count' => count($rows), 'billed' => $billed, 'paid' => $paid, 'balance' => $balance];
    }

    public function items(int $invoiceId): array
    {
        return $this->db->table('invoice_items')->where('invoice_id', $invoiceId)->get()->getResultArray();
    }

    public function payments(int $invoiceId): array
    {
        return $this->db->table('payments')->where('invoice_id', $invoiceId)->orderBy('id','desc')->get()->getResultArray();
    }

    public function recalcTotals(int $id): void
    {
        $items = $this->items($id);
        $subtotal = 0; $tax = 0;
        foreach ($items as $it) {
            $line = (float)$it['qty'] * (float)$it['unit_price'];
            $subtotal += $line;
            $tax += $line * ((float)$it['tax_pct'] / 100);
        }
        $inv = $this->find($id);
        $discount = (float)($inv['discount'] ?? 0);
        $total = max(0, $subtotal - $discount + $tax);
        $paid  = (float) $this->db->table('payments')
            ->selectSum('amount')->where('invoice_id', $id)->where('status','success')
            ->get()->getRow('amount');
        $balance = max(0, $total - $paid);
        $status = $balance <= 0 && $total > 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
        $this->update($id, [
            'subtotal' => $subtotal, 'tax' => $tax, 'total' => $total,
            'paid' => $paid, 'balance' => $balance, 'status' => $status,
        ]);
    }
}
