<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Payment
{
    public static function create(string $reference, string $purpose, int $userId, float $amount, ?int $bookingId, ?int $featuredId): int
    {
        return Database::insert('iw_payments', [
            'reference' => $reference,
            'purpose' => $purpose,
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => currency(),
            'booking_id' => $bookingId,
            'featured_purchase_id' => $featuredId,
        ]);
    }

    public static function findByReference(string $reference): ?array
    {
        return Database::one('SELECT * FROM iw_payments WHERE reference = ?', [$reference]);
    }

    public static function mark(int $id, string $status, string $channel = '', ?string $paidAt = null): void
    {
        Database::update('iw_payments', [
            'status' => $status,
            'channel' => $channel ?: null,
            'paid_at' => $paidAt ? date('Y-m-d H:i:s', strtotime($paidAt)) : ($status === 'success' ? date('Y-m-d H:i:s') : null),
        ], 'id = :id', ['id' => $id]);
    }
}
