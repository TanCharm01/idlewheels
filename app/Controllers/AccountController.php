<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Booking;
use App\Models\HostProfile;
use App\Models\SavedCar;
use App\Models\User;

class AccountController extends Controller
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        $tab = (string) input('tab', 'upcoming');
        if (!in_array($tab, ['upcoming', 'history', 'saved', 'profile'], true)) {
            $tab = 'upcoming';
        }
        $bookings = Booking::forRenter((int) $user['id']);
        $today = date('Y-m-d');
        $upcoming = array_values(array_filter($bookings, function ($b) use ($today) {
            return in_array($b['status'], ['pending_payment', 'awaiting_host', 'confirmed'], true) && $b['end_date'] > $today;
        }));
        $history = array_values(array_filter($bookings, function ($b) use ($upcoming) {
            return !in_array($b, $upcoming, true);
        }));
        $this->view('account/index', [
            'title' => 'My account',
            'tab' => $tab,
            'user' => $user,
            'upcoming' => array_reverse($upcoming),
            'history' => $history,
            'saved' => SavedCar::forUser((int) $user['id']),
            'host' => HostProfile::forUser((int) $user['id']),
        ]);
    }

    public function updateProfile(): void
    {
        $user = Auth::requireLogin();
        $data = $this->validate(['name' => 'required|min:2|max:100', 'phone' => 'phone|max:30'], '/account?tab=profile');
        User::updateProfile((int) $user['id'], $data['name'], (string) $data['phone']);
        flash('success', 'Profile updated.');
        redirect('/account', ['tab' => 'profile']);
    }

    public function updatePassword(): void
    {
        $user = Auth::requireLogin();
        if (!password_verify((string) ($_POST['current_password'] ?? ''), $user['password_hash'])) {
            Session::withInput([], ['current_password' => 'Your current password is incorrect.']);
            redirect('/account', ['tab' => 'profile']);
        }
        $this->validate(['password' => 'required|strong_password|confirmed'], '/account?tab=profile');
        if (password_verify((string) $_POST['password'], $user['password_hash'])) {
            Session::withInput([], ['password' => 'Choose a password that is different from your current one.']);
            redirect('/account', ['tab' => 'profile']);
        }
        User::updatePassword((int) $user['id'], (string) $_POST['password']);
        flash('success', 'Password changed.');
        redirect('/account', ['tab' => 'profile']);
    }
}
