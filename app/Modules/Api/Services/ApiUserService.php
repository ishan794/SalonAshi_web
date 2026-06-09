<?php

namespace App\Modules\Api\Services;

/**
 * Request-scoped authenticated user store for the API layer.
 * Populated by ApiAuthFilter on each authenticated request (no CI4 session used).
 */
class ApiUserService
{
    private array $data = [];

    public function set(array $data): void
    {
        $this->data = $data;
    }

    /** Return one key or the full array. */
    public function get(?string $key = null): mixed
    {
        return $key ? ($this->data[$key] ?? null) : $this->data;
    }

    public function id(): int        { return (int) ($this->data['id']       ?? 0); }
    public function role(): string   { return (string) ($this->data['role']  ?? ''); }
    public function staffId(): ?int  { return isset($this->data['staff_id']) ? (int) $this->data['staff_id'] : null; }
    public function branchId(): ?int { return isset($this->data['branch_id']) ? (int) $this->data['branch_id'] : null; }

    /** Check a single permission slug. super_admin bypasses all checks. */
    public function hasPerm(string $perm): bool
    {
        if ($this->role() === 'super_admin') return true;
        return in_array($perm, $this->data['perms'] ?? [], true);
    }

    /** True for roles that have full or near-full access. */
    public function isAdmin(): bool
    {
        return in_array($this->role(), ['super_admin', 'branch_manager'], true);
    }

    /** True for roles that can manage operations but not system settings. */
    public function isManager(): bool
    {
        return in_array($this->role(), ['super_admin', 'branch_manager', 'receptionist', 'accountant'], true);
    }

    /** True when this is a stylist-only user (limited to own data). */
    public function isStylist(): bool
    {
        return $this->role() === 'stylist';
    }

    public function isAuthenticated(): bool
    {
        return $this->data['id'] ?? 0 > 0;
    }
}
