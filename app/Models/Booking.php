<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Booking
{
    private const SELECT = 'SELECT b.*, c.title AS car_title, c.slug AS car_slug, c.location AS car_location, c.host_id,
        c.requires_approval,
        (SELECT p.path FROM iw_car_photos p WHERE p.car_id = c.id ORDER BY p.sort_order, p.id LIMIT 1) AS photo,
        u.name AS renter_name, u.email AS renter_email, u.phone AS renter_account_phone,
        h.display_name AS host_name, h.phone AS host_phone,
        (SELECT r.id FROM iw_reviews r WHERE r.booking_id = b.id) AS review_id
      FROM iw_bookings b
      JOIN iw_cars c ON c.id = b.car_id
      JOIN iw_users u ON u.id = b.renter_id
      LEFT JOIN iw_host_profiles h ON h.user_id = c.host_id';

    public static function newRef(): string
    {
        do {
            $ref = 'IW' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 7));
        } while (Database::value('SELECT id FROM iw_bookings WHERE ref = ?', [$ref]));
        return $ref;
    }

    public static function create(array $car, int $renterId, string $start, string $end, array $quote, string $phone, string $note): array
    {
        return Database::transaction(function () use ($car, $renterId, $start, $end, $quote, $phone, $note) {
            $ref = self::newRef();
            $id = Database::insert('iw_bookings', [
                'ref' => $ref,
                'car_id' => (int) $car['id'],
                'renter_id' => $renterId,
                'start_date' => $start,
                'end_date' => $end,
                'days' => $quote['days'],
                'price_per_day' => $quote['price_per_day'],
                'rental_amount' => $quote['rental'],
                'addons_amount' => $quote['addons_total'],
                'service_fee' => $quote['service_fee'],
                'service_fee_percent' => $quote['service_fee_percent'],
                'commission' => $quote['commission'],
                'commission_percent' => $quote['commission_percent'],
                'host_earnings' => $quote['host_earnings'],
                'total' => $quote['total'],
                'status' => 'pending_payment',
                'payment_status' => 'unpaid',
                'renter_phone' => $phone ?: null,
                'renter_note' => $note ?: null,
            ]);
            foreach ($quote['addons'] as $line) {
                Database::insert('iw_booking_addons', ['booking_id' => $id] + $line);
            }
            return ['id' => $id, 'ref' => $ref];
        });
    }

    public static function find(int $id): ?array
    {
        return Database::one(self::SELECT . ' WHERE b.id = ?', [$id]);
    }

    public static function findByRef(string $ref): ?array
    {
        return Database::one(self::SELECT . ' WHERE b.ref = ?', [$ref]);
    }

    public static function addons(int $bookingId): array
    {
        return Database::all('SELECT * FROM iw_booking_addons WHERE booking_id = ? ORDER BY id', [$bookingId]);
    }

    public static function forRenter(int $userId): array
    {
        return Database::all(self::SELECT . " WHERE b.renter_id = ? AND b.status <> 'pending_payment' OR (b.renter_id = ? AND b.status = 'pending_payment' AND b.created_at > (NOW() - INTERVAL 1 DAY)) ORDER BY b.start_date DESC", [$userId, $userId]);
    }

    public static function forHost(int $hostId): array
    {
        return Database::all(self::SELECT . " WHERE c.host_id = ? AND b.status <> 'pending_payment' ORDER BY b.start_date DESC", [$hostId]);
    }

    public static function allForAdmin(string $filter = ''): array
    {
        $where = "b.status <> 'pending_payment'";
        if ($filter === 'awaiting_host') {
            $where = "b.status = 'awaiting_host'";
        } elseif ($filter === 'upcoming') {
            $where = "b.status IN ('awaiting_host','confirmed') AND b.end_date >= CURDATE()";
        } elseif ($filter === 'refunds') {
            $where = "b.payment_status = 'refund_due'";
        } elseif ($filter === 'unpaid') {
            $where = "b.status = 'pending_payment'";
        }
        return Database::all(self::SELECT . " WHERE $where ORDER BY b.start_date DESC LIMIT 500");
    }

    public static function update(int $id, array $data): void
    {
        Database::update('iw_bookings', $data + ['updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }

    public static function isCompleted(array $b): bool
    {
        return in_array($b['status'], ['confirmed', 'completed'], true) && $b['end_date'] <= date('Y-m-d');
    }

    public static function displayStatus(array $b): string
    {
        return self::isCompleted($b) ? 'completed' : $b['status'];
    }
}
