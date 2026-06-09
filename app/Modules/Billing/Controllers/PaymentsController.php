<?php

namespace App\Modules\Billing\Controllers;

use App\Controllers\BaseController;

class PaymentsController extends BaseController
{
    public function index()
    {
        $filters = [
            'q'      => trim((string) $this->request->getGet('q')),
            'method' => $this->request->getGet('method'),
            'from'   => $this->request->getGet('from'),
            'to'     => $this->request->getGet('to'),
        ];
        foreach (['from','to'] as $k) {
            if ($filters[$k] && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$k])) $filters[$k] = null;
        }
        $allowedMethods = ['cash','card','bank_transfer','mobile_wallet','online'];
        if ($filters['method'] && ! in_array($filters['method'], $allowedMethods, true)) $filters['method'] = null;

        // Closure that applies all active filters to a builder (shared by totals + count + page).
        $applyFilters = function ($b) use ($filters) {
            $b->where('p.status', 'success');
            if (! empty($filters['method'])) $b->where('p.method', $filters['method']);
            if (! empty($filters['from']))   $b->where('DATE(p.paid_at) >=', $filters['from']);
            if (! empty($filters['to']))     $b->where('DATE(p.paid_at) <=', $filters['to']);
            if (! empty($filters['q'])) {
                $term = $filters['q'];
                $b->groupStart()
                    ->like('i.invoice_no', $term)
                    ->orLike('c.full_name', $term)
                    ->orLike('c.mobile', $term)
                    ->orLike('p.txn_ref', $term)
                    ->groupEnd();
            }
            return $b;
        };

        // Totals across the ENTIRE filtered set (not just the current page).
        $totRow = $applyFilters(
            db_connect()->table('payments p')
                ->select('COUNT(*) cnt, COALESCE(SUM(p.amount),0) amt')
                ->join('invoices i', 'i.id = p.invoice_id', 'left')
                ->join('customers c', 'c.id = i.customer_id', 'left')
        )->get()->getRowArray();
        $totalCount  = (int) ($totRow['cnt'] ?? 0);
        $totalAmount = (float) ($totRow['amt'] ?? 0);

        // Per-method breakdown across the whole filtered set.
        $byMethodRows = $applyFilters(
            db_connect()->table('payments p')
                ->select('p.method, COALESCE(SUM(p.amount),0) amt')
                ->join('invoices i', 'i.id = p.invoice_id', 'left')
                ->join('customers c', 'c.id = i.customer_id', 'left')
        )->groupBy('p.method')->get()->getResultArray();
        $byMethod = [];
        foreach ($byMethodRows as $m) $byMethod[$m['method']] = (float) $m['amt'];
        arsort($byMethod);

        // Pagination
        $perPage    = max(5, min(100, (int) ($this->request->getGet('per_page') ?: 20)));
        $page       = max(1, (int) $this->request->getGet('page'));
        $totalPages = max(1, (int) ceil($totalCount / $perPage));
        if ($page > $totalPages) $page = $totalPages;

        $rows = $applyFilters(
            db_connect()->table('payments p')
                ->select('p.*, i.invoice_no, i.customer_id, c.full_name AS customer_name, c.mobile AS customer_mobile')
                ->join('invoices i', 'i.id = p.invoice_id', 'left')
                ->join('customers c', 'c.id = i.customer_id', 'left')
                ->orderBy('p.paid_at', 'desc')
        )->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return view('layout/admin', [
            'title'   => 'Payments',
            'content' => view('App\Modules\Billing\Views\payments_index', [
                'rows'    => $rows,
                'filters' => $filters,
                'totals'  => [
                    'count'    => $totalCount,
                    'amount'   => $totalAmount,
                    'byMethod' => $byMethod,
                ],
                'page'    => [
                    'total'       => $totalCount,
                    'totalPages'  => $totalPages,
                    'currentPage' => $page,
                    'perPage'     => $perPage,
                    'firstIndex'  => $totalCount === 0 ? 0 : (($page - 1) * $perPage + 1),
                    'lastIndex'   => min($page * $perPage, $totalCount),
                ],
            ]),
        ]);
    }
}
