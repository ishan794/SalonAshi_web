<?php

namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name', 'email', 'password_hash', 'role_id', 'branch_id',
        'phone', 'status', 'last_login_at', 'expo_push_token',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function withRole(int $id): ?array
    {
        return $this->db->table('users u')
            ->select('u.*, r.name AS role_name, r.label AS role_label, b.name AS branch_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->where('u.id', $id)
            ->get()->getRowArray();
    }
}
