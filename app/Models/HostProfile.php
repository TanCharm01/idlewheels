<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class HostProfile
{
    public static function forUser(int $userId): ?array
    {
        return Database::one('SELECT * FROM iw_host_profiles WHERE user_id = ?', [$userId]);
    }

    public static function apply(int $userId, array $data): void
    {
        $existing = self::forUser($userId);
        if ($existing) {
            $status = $existing['status'] === 'approved' ? 'approved' : 'pending';
            Database::update('iw_host_profiles', $data + ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'user_id = :uid', ['uid' => $userId]);
        } else {
            Database::insert('iw_host_profiles', $data + ['user_id' => $userId, 'status' => 'pending']);
        }
    }

    public static function allForAdmin(): array
    {
        return Database::all(
            'SELECT h.*, u.email, u.name AS user_name,
               (SELECT COUNT(*) FROM iw_cars c WHERE c.host_id = h.user_id) AS car_count
             FROM iw_host_profiles h JOIN iw_users u ON u.id = h.user_id
             WHERE u.password_hash <> \'!\'
             ORDER BY FIELD(h.status, \'pending\', \'approved\', \'rejected\'), h.created_at DESC'
        );
    }

    public static function setStatus(int $id, string $status, string $note): void
    {
        Database::update('iw_host_profiles', ['status' => $status, 'admin_note' => $note ?: null, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }
}
