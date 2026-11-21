<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Addon
{
    public static function active(): array
    {
        return Database::all('SELECT * FROM iw_addons WHERE active = 1 ORDER BY sort_order, id');
    }

    public static function all(): array
    {
        return Database::all('SELECT * FROM iw_addons ORDER BY active DESC, sort_order, id');
    }

    public static function save(?int $id, array $data): void
    {
        if ($id) {
            Database::update('iw_addons', $data, 'id = :id', ['id' => $id]);
        } else {
            Database::insert('iw_addons', $data);
        }
    }
}
