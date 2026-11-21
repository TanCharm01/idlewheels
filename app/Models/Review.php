<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Review
{
    public static function forCar(int $carId): array
    {
        return Database::all(
            'SELECT r.*, u.name AS author FROM iw_reviews r JOIN iw_users u ON u.id = r.renter_id
             WHERE r.car_id = ? AND r.hidden = 0 ORDER BY r.created_at DESC',
            [$carId]
        );
    }

    public static function create(array $booking, int $rating, string $comment): void
    {
        Database::insert('iw_reviews', [
            'booking_id' => (int) $booking['id'],
            'car_id' => (int) $booking['car_id'],
            'renter_id' => (int) $booking['renter_id'],
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    public static function allForAdmin(): array
    {
        return Database::all(
            'SELECT r.*, u.name AS author, c.title AS car_title, c.slug AS car_slug
             FROM iw_reviews r JOIN iw_users u ON u.id = r.renter_id JOIN iw_cars c ON c.id = r.car_id
             ORDER BY r.created_at DESC'
        );
    }

    public static function setHidden(int $id, bool $hidden): void
    {
        Database::update('iw_reviews', ['hidden' => $hidden ? 1 : 0], 'id = :id', ['id' => $id]);
    }

    public static function shortName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) < 2) {
            return $parts[0] ?? 'Renter';
        }
        return $parts[0] . ' ' . mb_substr(end($parts), 0, 1) . '.';
    }
}
