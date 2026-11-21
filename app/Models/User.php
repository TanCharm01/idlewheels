<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM iw_users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::one('SELECT * FROM iw_users WHERE email = ?', [strtolower(trim($email))]);
    }

    public static function create(string $name, string $email, string $phone, string $password): int
    {
        return Database::insert('iw_users', [
            'name' => $name,
            'email' => strtolower(trim($email)),
            'phone' => $phone ?: null,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public static function updateProfile(int $id, string $name, string $phone): void
    {
        Database::update('iw_users', ['name' => $name, 'phone' => $phone ?: null], 'id = :id', ['id' => $id]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        Database::update('iw_users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], 'id = :id', ['id' => $id]);
    }

    public static function allForAdmin(string $q = ''): array
    {
        $sql = 'SELECT u.*, h.status AS host_status,
                  (SELECT COUNT(*) FROM iw_bookings b WHERE b.renter_id = u.id) AS booking_count
                FROM iw_users u LEFT JOIN iw_host_profiles h ON h.user_id = u.id
                WHERE u.password_hash <> \'!\'';
        $params = [];
        if ($q !== '') {
            $sql .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
            $params = ["%$q%", "%$q%"];
        }
        return Database::all($sql . ' ORDER BY u.created_at DESC LIMIT 500', $params);
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::update('iw_users', ['status' => $status], 'id = :id', ['id' => $id]);
    }

    public static function setRole(int $id, string $role): void
    {
        Database::update('iw_users', ['role' => $role], 'id = :id', ['id' => $id]);
    }
}
