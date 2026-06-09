<?php

namespace App\Modules\CustomerPortal\Models;

use CodeIgniter\Model;

class CustomerOtpModel extends Model
{
    protected $table         = 'customer_otps';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['customer_id', 'code', 'expires_at', 'used_at', 'ip_address', 'created_at'];

    /** Create a fresh 6-digit code valid 10 min. Invalidates older unused codes for this customer. */
    public function issue(int $customerId, ?string $ip = null): string
    {
        $this->where('customer_id', $customerId)->where('used_at IS NULL', null, false)->set(['used_at' => date('Y-m-d H:i:s')])->update();
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->insert([
            'customer_id' => $customerId,
            'code'        => $code,
            'expires_at'  => date('Y-m-d H:i:s', time() + 600),
            'ip_address'  => $ip,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return $code;
    }

    /** Return the OTP row if a still-valid unused code matches. */
    public function findValid(int $customerId, string $code): ?array
    {
        $row = $this->where('customer_id', $customerId)
            ->where('code', preg_replace('/\D+/', '', $code))
            ->where('used_at IS NULL', null, false)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();
        return $row ?: null;
    }

    public function consume(int $id): void
    {
        $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }
}
