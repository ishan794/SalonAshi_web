<?php

namespace App\Modules\Services\Models;

use CodeIgniter\Model;

class ServiceTypeModel extends Model
{
    protected $table         = 'service_types';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'slug', 'color', 'multiplier', 'description', 'is_default', 'is_active', 'sort_order'];

    public function active(): array
    {
        return $this->where('is_active', 1)->orderBy('sort_order')->findAll();
    }

    public function defaultType(): ?array
    {
        $row = $this->where('is_default', 1)->orderBy('sort_order')->first();
        if ($row) return $row;
        $row = $this->where('is_active', 1)->orderBy('sort_order')->first();
        return $row ?: null;
    }

    /** Returns the effective price for a service under a type. */
    public function priceFor(float $servicePrice, int $typeId): float
    {
        $t = $this->find($typeId);
        if (! $t) return $servicePrice;
        return round($servicePrice * (float) $t['multiplier'], 2);
    }
}
