<?php
declare(strict_types=1);

namespace App\Core;

class Env
{
    private static array $vars = [];

    public static function load(string $file): void
    {
        if (!is_file($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && substr($value, -1) === $value[0]) {
                $value = substr($value, 1, -1);
            }
            self::$vars[$key] = $value;
        }
    }

    public static function get(string $key, $default = null)
    {
        $real = getenv($key);
        if ($real !== false && $real !== '') {
            return $real;
        }
        if (!array_key_exists($key, self::$vars)) {
            return $default;
        }
        $v = self::$vars[$key];
        $lower = strtolower($v);
        if ($lower === 'true' || $lower === 'false') {
            return $lower === 'true';
        }
        return $v;
    }
}
