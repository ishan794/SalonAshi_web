<?php

namespace App\Modules\Customers\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table         = 'customers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'branch_id', 'full_name', 'mobile', 'email', 'gender', 'birthday',
        'address', 'preferred_stylist_id', 'notes', 'membership', 'loyalty_points', 'status',
    ];

    public function search(?string $q, int $limit = 50): array
    {
        $builder = $this->orderBy('id', 'desc');
        if ($q) {
            $builder->groupStart()
                ->like('full_name', $q)
                ->orLike('mobile', $q)
                ->orLike('email', $q)
                ->groupEnd();
        }
        return $builder->limit($limit)->find();
    }

    /**
     * Paginated search: returns rows + total + page metadata.
     * Used by /admin/customers — the autocomplete still uses search().
     */
    public function paginatedSearch(?string $q, int $perPage = 20, int $page = 1): array
    {
        $perPage = max(5, min(100, $perPage));
        $page    = max(1, $page);

        // Digit-normalised phone "core": last 9 subscriber digits (drops 0/country code),
        // matched against a punctuation-stripped mobile column so "0771…", "+9477…",
        // "077 1234" and "9477…" all resolve to the same record.
        $digits = $q ? preg_replace('/\D+/', '', $q) : '';
        $core   = strlen($digits) >= 9 ? substr($digits, -9) : ltrim($digits, '0');

        // Apply the same WHERE to a FRESH builder. $this->db->table() returns a new
        // builder each call (unlike Model::builder() which is shared), so the count
        // and the row query are fully independent.
        $applyWhere = function ($b) use ($q, $core) {
            if ($q) {
                $b->groupStart()
                    ->like('full_name', $q)
                    ->orLike('email', $q)
                    ->orLike('mobile', $q);
                if ($core !== '') {
                    $expr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(mobile,' ',''),'-',''),'(',''),')',''),'+','') LIKE '%{$core}%'";
                    $b->orWhere($expr, null, false);
                }
                $b->groupEnd();
            }
            return $b;
        };

        $total      = (int) $applyWhere($this->db->table($this->table))->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;

        $rows = $applyWhere($this->db->table($this->table))
            ->orderBy('id', 'desc')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        return [
            'rows'        => $rows,
            'total'       => $total,
            'totalPages'  => $totalPages,
            'currentPage' => $page,
            'perPage'     => $perPage,
            'firstIndex'  => $total === 0 ? 0 : (($page - 1) * $perPage + 1),
            'lastIndex'   => min($page * $perPage, $total),
        ];
    }
}
