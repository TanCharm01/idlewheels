<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Car
{
    private const HOLD_SQL = "(b.status IN ('awaiting_host','confirmed') OR (b.status = 'pending_payment' AND b.created_at > (NOW() - INTERVAL 30 MINUTE)))";

    private const SELECT = "SELECT c.*, cat.name AS category_name, h.display_name AS host_name, h.created_at AS host_since,
        (SELECT p.path FROM iw_car_photos p WHERE p.car_id = c.id ORDER BY p.sort_order, p.id LIMIT 1) AS photo,
        (SELECT ROUND(AVG(r.rating), 1) FROM iw_reviews r WHERE r.car_id = c.id AND r.hidden = 0) AS rating,
        (SELECT COUNT(*) FROM iw_reviews r WHERE r.car_id = c.id AND r.hidden = 0) AS review_count,
        (c.featured_until IS NOT NULL AND c.featured_until > NOW()) AS is_featured
      FROM iw_cars c
      LEFT JOIN iw_categories cat ON cat.id = c.category_id
      LEFT JOIN iw_host_profiles h ON h.user_id = c.host_id";

    public static function search(array $f, int $limit = 60): array
    {
        $where = ["c.status = 'approved'"];
        $params = [];
        if (!empty($f['location'])) {
            $where[] = 'c.location LIKE ?';
            $params[] = '%' . $f['location'] . '%';
        }
        if (!empty($f['type'])) {
            $where[] = 'cat.slug = ?';
            $params[] = $f['type'];
        }
        if (!empty($f['seats'])) {
            $where[] = 'c.seats >= ?';
            $params[] = (int) $f['seats'];
        }
        if (!empty($f['transmission']) && in_array($f['transmission'], ['Automatic', 'Manual'], true)) {
            $where[] = 'c.transmission = ?';
            $params[] = $f['transmission'];
        }
        if (!empty($f['max_price']) && is_numeric($f['max_price'])) {
            $where[] = 'c.price_per_day <= ?';
            $params[] = (float) $f['max_price'];
        }
        if (!empty($f['q'])) {
            $where[] = '(c.title LIKE ? OR c.make LIKE ? OR c.model LIKE ?)';
            array_push($params, '%' . $f['q'] . '%', '%' . $f['q'] . '%', '%' . $f['q'] . '%');
        }
        if (!empty($f['from']) && !empty($f['to']) && $f['to'] > $f['from']) {
            $where[] = 'NOT EXISTS (SELECT 1 FROM iw_bookings b WHERE b.car_id = c.id AND ' . self::HOLD_SQL . ' AND b.start_date < ? AND b.end_date > ?)';
            array_push($params, $f['to'], $f['from']);
        }
        $order = 'is_featured DESC, ';
        switch ($f['sort'] ?? '') {
            case 'price_asc':
                $order .= 'c.price_per_day ASC';
                break;
            case 'price_desc':
                $order .= 'c.price_per_day DESC';
                break;
            default:
                $order .= 'c.created_at DESC';
        }
        return Database::all(self::SELECT . ' WHERE ' . implode(' AND ', $where) . " ORDER BY $order LIMIT " . (int) $limit, $params);
    }

    public static function featured(int $limit = 6): array
    {
        return Database::all(self::SELECT . " WHERE c.status = 'approved' ORDER BY is_featured DESC, rating DESC, c.created_at DESC LIMIT " . (int) $limit);
    }

    public static function findLiveBySlug(string $slug): ?array
    {
        return Database::one(self::SELECT . " WHERE c.slug = ? AND c.status = 'approved'", [$slug]);
    }

    public static function find(int $id): ?array
    {
        return Database::one(self::SELECT . ' WHERE c.id = ?', [$id]);
    }

    public static function forHost(int $hostId): array
    {
        return Database::all(self::SELECT . ' WHERE c.host_id = ? ORDER BY c.created_at DESC', [$hostId]);
    }

    public static function allForAdmin(string $status = ''): array
    {
        if ($status !== '') {
            return Database::all(self::SELECT . ' WHERE c.status = ? ORDER BY c.created_at DESC', [$status]);
        }
        return Database::all(self::SELECT . ' ORDER BY FIELD(c.status, \'pending\', \'approved\', \'rejected\', \'hidden\'), c.created_at DESC');
    }

    public static function photos(int $carId): array
    {
        return Database::all('SELECT * FROM iw_car_photos WHERE car_id = ? ORDER BY sort_order, id', [$carId]);
    }

    public static function locations(): array
    {
        $rows = Database::all("SELECT DISTINCT location FROM iw_cars WHERE status = 'approved'");
        $set = [];
        foreach ($rows as $r) {
            foreach (explode(',', $r['location']) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $set[$part] = true;
                }
            }
        }
        $list = array_keys($set);
        sort($list);
        return $list;
    }

    public static function heldRanges(int $carId, ?int $exceptBookingId = null): array
    {
        $sql = 'SELECT b.start_date, b.end_date FROM iw_bookings b WHERE b.car_id = ? AND ' . self::HOLD_SQL . ' AND b.end_date >= CURDATE()';
        $params = [$carId];
        if ($exceptBookingId) {
            $sql .= ' AND b.id <> ?';
            $params[] = $exceptBookingId;
        }
        return Database::all($sql, $params);
    }

    public static function isAvailable(int $carId, string $start, string $end): bool
    {
        $n = Database::value(
            'SELECT COUNT(*) FROM iw_bookings b WHERE b.car_id = ? AND ' . self::HOLD_SQL . ' AND b.start_date < ? AND b.end_date > ?',
            [$carId, $end, $start]
        );
        return (int) $n === 0;
    }

    public static function uniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = slugify($title);
        $slug = $base;
        $i = 2;
        while (Database::value('SELECT id FROM iw_cars WHERE slug = ?' . ($exceptId ? ' AND id <> ' . (int) $exceptId : ''), [$slug])) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public static function create(array $data, array $photos): int
    {
        return Database::transaction(function () use ($data, $photos) {
            $id = Database::insert('iw_cars', $data);
            foreach (array_values($photos) as $i => $p) {
                Database::insert('iw_car_photos', ['car_id' => $id, 'path' => $p, 'sort_order' => $i]);
            }
            return $id;
        });
    }

    public static function updateCar(int $id, array $data): void
    {
        Database::update('iw_cars', $data + ['updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }

    public static function addPhotos(int $carId, array $paths): void
    {
        $next = (int) Database::value('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM iw_car_photos WHERE car_id = ?', [$carId]);
        foreach ($paths as $p) {
            Database::insert('iw_car_photos', ['car_id' => $carId, 'path' => $p, 'sort_order' => $next++]);
        }
    }

    public static function removePhoto(int $carId, int $photoId): ?string
    {
        $row = Database::one('SELECT path FROM iw_car_photos WHERE id = ? AND car_id = ?', [$photoId, $carId]);
        if ($row) {
            Database::run('DELETE FROM iw_car_photos WHERE id = ?', [$photoId]);
            return $row['path'];
        }
        return null;
    }

    public static function setStatus(int $id, string $status, string $note = ''): void
    {
        Database::update('iw_cars', ['status' => $status, 'admin_note' => $note ?: null, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }

    public static function extendFeatured(int $id, int $days): array
    {
        $car = Database::one('SELECT featured_until FROM iw_cars WHERE id = ?', [$id]);
        $from = ($car && $car['featured_until'] && strtotime($car['featured_until']) > time()) ? strtotime($car['featured_until']) : time();
        $start = date('Y-m-d H:i:s', $from);
        $end = date('Y-m-d H:i:s', $from + $days * 86400);
        Database::update('iw_cars', ['featured_until' => $end], 'id = :id', ['id' => $id]);
        return [$start, $end];
    }

    public static function endFeatured(int $id): void
    {
        Database::update('iw_cars', ['featured_until' => null], 'id = :id', ['id' => $id]);
    }

    public static function features(array $car): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) ($car['features'] ?? '')))));
    }
}
