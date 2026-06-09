<?php

namespace App\Modules\Reviews\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table         = 'reviews';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'customer_id', 'appointment_id', 'staff_id',
        'reviewer_name', 'reviewer_avatar_url',
        'rating', 'title', 'body',
        'source', 'source_id', 'source_url',
        'status', 'is_featured',
        'source_created_at', 'admin_response',
    ];

    /** Latest approved reviews — used on home page. */
    public function latestApproved(int $limit = 6): array
    {
        return $this->where('status', 'approved')
            ->orderBy('is_featured', 'DESC')
            ->orderBy('COALESCE(source_created_at, created_at)', 'DESC', false)
            ->findAll($limit);
    }

    /** Pending list — admin moderation queue. */
    public function pending(): array
    {
        return $this->where('status', 'pending')->orderBy('id', 'DESC')->findAll();
    }

    public function withFilters(?string $status = null, ?string $source = null, ?int $minRating = null): array
    {
        $q = $this->orderBy('id', 'DESC');
        if ($status)    $q->where('status', $status);
        if ($source)    $q->where('source', $source);
        if ($minRating) $q->where('rating >=', $minRating);
        return $q->findAll(200);
    }

    /** Aggregate stats for admin dashboard / public summary. */
    public function summary(): array
    {
        $row = $this->select('COUNT(*) total, AVG(rating) avg_rating, SUM(CASE WHEN status="pending" THEN 1 ELSE 0 END) pending_count, SUM(CASE WHEN status="approved" THEN 1 ELSE 0 END) approved_count')
            ->where('status !=', 'rejected')
            ->first();
        return [
            'total'         => (int)   ($row['total']          ?? 0),
            'avg_rating'    => round((float) ($row['avg_rating']  ?? 0), 1),
            'pending_count' => (int)   ($row['pending_count']  ?? 0),
            'approved_count'=> (int)   ($row['approved_count'] ?? 0),
        ];
    }

    /**
     * Upsert a Google-imported review by source_id. Returns ['action' => 'inserted'|'updated'|'skipped'].
     */
    public function upsertGoogle(array $row): array
    {
        $existing = $this->where('source', 'google')
                          ->where('source_id', $row['source_id'])
                          ->first();
        if ($existing) {
            // Only refresh body/rating/avatar — never overwrite admin status.
            $this->update($existing['id'], [
                'reviewer_name'       => $row['reviewer_name'],
                'reviewer_avatar_url' => $row['reviewer_avatar_url'] ?? null,
                'rating'              => $row['rating'],
                'body'                => $row['body'],
                'source_url'          => $row['source_url'] ?? null,
                'source_created_at'   => $row['source_created_at'] ?? null,
            ]);
            return ['action' => 'updated', 'id' => (int) $existing['id']];
        }
        $id = $this->insert(array_merge([
            'source' => 'google',
            'status' => 'approved', // Google reviews are auto-approved
        ], $row), true);
        return ['action' => 'inserted', 'id' => (int) $id];
    }
}
