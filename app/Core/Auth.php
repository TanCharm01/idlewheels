<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\HostProfile;
use App\Models\User;

class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('user_id');
            if ($id) {
                $u = User::find((int) $id);
                self::$user = ($u && $u['status'] === 'active') ? $u : null;
                if (!self::$user) {
                    Session::forget('user_id');
                }
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u !== null && $u['role'] === 'admin';
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        self::$user = $user;
        self::$loaded = true;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
    }

    public static function requireLogin(): array
    {
        $u = self::user();
        if (!$u) {
            flash('info', 'Please log in to continue.');
            redirect('/login', ['return' => request_path() . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING'])]);
        }
        return $u;
    }

    public static function requireAdmin(): array
    {
        $u = self::requireLogin();
        if ($u['role'] !== 'admin') {
            abort(403, 'This area is for site admins only.');
        }
        return $u;
    }

    public static function requireApprovedHost(): array
    {
        $u = self::requireLogin();
        $profile = HostProfile::forUser((int) $u['id']);
        if (!$profile || $profile['status'] !== 'approved') {
            flash('info', $profile ? 'Your host application is still being reviewed.' : 'Apply to host before adding a car.');
            redirect('/host');
        }
        return $profile;
    }
}
