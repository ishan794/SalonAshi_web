<?php

namespace App\Modules\Settings\Models;

class SettingModel
{
    private static ?array $cache = null;

    private function db()  { return db_connect(); }
    private function tbl() { return $this->db()->table('settings'); }

    /** Get one value */
    public function get(string $k, $default = null)
    {
        $this->preload();
        return self::$cache[$k] ?? $default;
    }

    /** Get many values matching a prefix, with the prefix stripped */
    public function group(string $prefix): array
    {
        $this->preload();
        $out = [];
        foreach (self::$cache as $k => $v) {
            if (str_starts_with($k, $prefix)) {
                $out[substr($k, strlen($prefix))] = $v;
            }
        }
        return $out;
    }

    /** Get all values (raw) */
    public function all(): array
    {
        $this->preload();
        return self::$cache;
    }

    /** Set one value */
    public function set(string $k, $v): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db()->query(
            "INSERT INTO settings (k, v, updated_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE v = VALUES(v), updated_at = VALUES(updated_at)",
            [$k, (string) $v, $now]
        );
        self::$cache[$k] = (string) $v;
    }

    /** Bulk-set from an associative array */
    public function setMany(array $pairs): void
    {
        foreach ($pairs as $k => $v) {
            $this->set((string) $k, $v);
        }
    }

    private function preload(): void
    {
        if (self::$cache !== null) return;
        self::$cache = [];
        foreach ($this->tbl()->get()->getResultArray() as $r) {
            self::$cache[$r['k']] = $r['v'];
        }
    }
}
