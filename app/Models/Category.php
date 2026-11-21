<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Category
{
    public static function all(): array
    {
        return Database::all(
            'SELECT c.*, (SELECT COUNT(*) FROM iw_cars x WHERE x.category_id = c.id AND x.status = \'approved\') AS car_count
             FROM iw_categories c ORDER BY c.sort_order, c.name'
        );
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM iw_categories WHERE id = ?', [$id]);
    }

    public static function save(?int $id, array $data): void
    {
        if ($id) {
            Database::update('iw_categories', $data, 'id = :id', ['id' => $id]);
        } else {
            Database::insert('iw_categories', $data);
        }
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM iw_categories WHERE id = ?', [$id]);
    }
}
