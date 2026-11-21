<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SavedCar
{
    public static function isSaved(int $userId, int $carId): bool
    {
        return (bool) Database::value('SELECT 1 FROM iw_saved_cars WHERE user_id = ? AND car_id = ?', [$userId, $carId]);
    }

    public static function toggle(int $userId, int $carId): bool
    {
        if (self::isSaved($userId, $carId)) {
            Database::run('DELETE FROM iw_saved_cars WHERE user_id = ? AND car_id = ?', [$userId, $carId]);
            return false;
        }
        Database::insert('iw_saved_cars', ['user_id' => $userId, 'car_id' => $carId]);
        return true;
    }

    public static function forUser(int $userId): array
    {
        return Database::all(
            "SELECT c.*, s.created_at AS saved_at,
               (SELECT p.path FROM iw_car_photos p WHERE p.car_id = c.id ORDER BY p.sort_order, p.id LIMIT 1) AS photo
             FROM iw_saved_cars s JOIN iw_cars c ON c.id = s.car_id
             WHERE s.user_id = ? AND c.status = 'approved' ORDER BY s.created_at DESC",
            [$userId]
        );
    }
}
