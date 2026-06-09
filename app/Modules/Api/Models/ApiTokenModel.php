<?php

namespace App\Modules\Api\Models;

use CodeIgniter\Model;

class ApiTokenModel extends Model
{
    protected $table         = 'user_api_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'user_id', 'token_hash', 'name', 'last_used_at', 'expires_at', 'created_at',
    ];

    /**
     * Issue a new token for a user.
     * Returns the raw (plaintext) 64-char hex token — shown only once.
     */
    public function issue(int $userId, ?string $deviceName = null): string
    {
        $raw  = bin2hex(random_bytes(32));   // 64 hex chars
        $hash = hash('sha256', $raw);

        $this->insert([
            'user_id'    => $userId,
            'token_hash' => $hash,
            'name'       => $deviceName,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $raw;
    }

    /**
     * Validate a raw token string.
     * Returns the token DB row on success, or null if missing/expired.
     */
    public function findValidToken(string $raw): ?array
    {
        $hash = hash('sha256', $raw);
        return $this->where('token_hash', $hash)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Sliding expiry — extend 30 days from now and record last-used timestamp.
     */
    public function slideExpiry(int $id): void
    {
        $this->update($id, [
            'expires_at'   => date('Y-m-d H:i:s', strtotime('+30 days')),
            'last_used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Revoke a single token by its raw value. */
    public function revoke(string $raw): void
    {
        $hash = hash('sha256', $raw);
        $this->where('token_hash', $hash)->delete();
    }

    /** Revoke all tokens for a user (logout all devices). */
    public function revokeAllForUser(int $userId): void
    {
        $this->where('user_id', $userId)->delete();
    }
}
