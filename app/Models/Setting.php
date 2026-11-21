<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Setting
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                foreach (Database::all('SELECT name, value FROM iw_settings') as $row) {
                    self::$cache[$row['name']] = $row['value'];
                }
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }
        return self::$cache;
    }

    public static function get(string $name, string $default = ''): string
    {
        $all = self::all();
        return $all[$name] ?? $default;
    }

    public static function num(string $name, float $default = 0): float
    {
        $v = self::get($name, (string) $default);
        return is_numeric($v) ? (float) $v : $default;
    }

    public static function set(string $name, string $value): void
    {
        Database::run('INSERT INTO iw_settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)', [$name, $value]);
        self::$cache = null;
    }
}
