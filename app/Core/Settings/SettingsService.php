<?php

namespace App\Core\Settings;

use CodeIgniter\Database\BaseConnection;

class SettingsService
{
    private static array $cache = [];
    private static bool $loaded = false;
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->loadAll();

        if (! isset(self::$cache[$key])) {
            return $default;
        }

        return $this->cast(self::$cache[$key]['value'], self::$cache[$key]['type']);
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        $strValue = is_array($value) ? json_encode($value) : (string) $value;

        $exists = $this->db->table('settings')->where('key', $key)->countAllResults();

        if ($exists) {
            $this->db->table('settings')->where('key', $key)->update([
                'value'      => $strValue,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->table('settings')->insert([
                'key'        => $key,
                'value'      => $strValue,
                'group'      => $group,
                'type'       => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Cache güncelle
        self::$cache[$key] = ['value' => $strValue, 'type' => $type, 'group' => $group];

        // CI4 cache'i de temizle
        cache()->delete('settings_all');
    }

    public function getGroup(string $group): array
    {
        $this->loadAll();
        $result = [];

        foreach (self::$cache as $key => $row) {
            if ($row['group'] === $group) {
                $result[$key] = $this->cast($row['value'], $row['type']);
            }
        }

        return $result;
    }

    public function getAllForAdmin(): array
    {
        $rows = $this->db->table('settings')->orderBy('group')->orderBy('key')->get()->getResultArray();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['group']][] = $row;
        }

        return $grouped;
    }

    public function bustCache(): void
    {
        self::$cache  = [];
        self::$loaded = false;
        cache()->delete('settings_all');
    }

    private function loadAll(): void
    {
        if (self::$loaded) {
            return;
        }

        // Önce CI4 cache'den dene
        $cached = cache()->get('settings_all');

        if ($cached !== null) {
            self::$cache  = $cached;
            self::$loaded = true;
            return;
        }

        $rows = $this->db->table('settings')->get()->getResultArray();

        foreach ($rows as $row) {
            self::$cache[$row['key']] = [
                'value' => $row['value'],
                'type'  => $row['type'],
                'group' => $row['group'],
            ];
        }

        cache()->save('settings_all', self::$cache, 600);
        self::$loaded = true;
    }

    private function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'bool'  => (bool) $value,
            'int'   => (int) $value,
            'json'  => json_decode((string) $value, true),
            default => (string) ($value ?? ''),
        };
    }
}
