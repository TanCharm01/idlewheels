<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class FeaturedPurchase
{
    public static function create(int $carId, int $hostId, int $days, float $amount, string $source = 'purchase', string $status = 'pending'): int
    {
        return Database::insert('iw_featured_purchases', [
            'car_id' => $carId,
            'host_id' => $hostId,
            'days' => $days,
            'amount' => $amount,
            'source' => $source,
            'status' => $status,
        ]);
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM iw_featured_purchases WHERE id = ?', [$id]);
    }

    public static function markPaid(int $id, string $startsAt, string $endsAt): void
    {
        Database::update('iw_featured_purchases', ['status' => 'paid', 'starts_at' => $startsAt, 'ends_at' => $endsAt], 'id = :id', ['id' => $id]);
    }
}
