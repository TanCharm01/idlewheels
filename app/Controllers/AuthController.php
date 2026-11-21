<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    private function returnTo(): string
    {
        $r = (string) input('return', '/account');
        return (str_starts_with($r, '/') && !str_starts_with($r, '//')) ? $r : '/account';
    }

    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect($this->returnTo());
        }
        $this->view('auth/login', ['title' => 'Log in', 'return' => $this->returnTo()]);
    }

    public function login(): void
    {
        $data = $this->validate(['email' => 'required|email', 'password' => 'required']);
        // Slow brute force down a little per session.
        $tries = (int) Session::get('login_tries', 0);
        if ($tries >= 8) {
            usleep(1000000);
        }
        $user = User::findByEmail($data['email']);
        if (!$user || !password_verify((string) $_POST['password'], $user['password_hash'])) {
            Session::set('login_tries', $tries + 1);
            Session::withInput($_POST, ['email' => 'That email and password don\'t match an account.']);
            redirect('/login', ['return' => $this->returnTo()]);
        }
        if ($user['status'] !== 'active') {
            Session::withInput($_POST, ['email' => 'This account has been blocked. Contact us for help.']);
            redirect('/login');
        }
        Session::set('login_tries', 0);
        Auth::login($user);
        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect($this->returnTo());
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            redirect('/account');
        }
        $this->view('auth/register', ['title' => 'Create an account', 'return' => $this->returnTo()]);
    }

    public function register(): void
    {
        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:190',
            'phone' => 'phone|max:30',
            'password' => 'required|strong_password|confirmed',
        ]);
        if (empty($_POST['terms'])) {
            Session::withInput($_POST, ['terms' => 'Please accept the Terms to create an account.']);
            back('/register');
        }
        if (User::findByEmail($data['email'])) {
            Session::withInput($_POST, ['email' => 'An account with this email already exists. Log in instead.']);
            back('/register');
        }
        $id = User::create($data['name'], $data['email'], (string) $data['phone'], (string) $_POST['password']);
        Auth::login(User::find($id));
        flash('success', 'Your account is ready. Welcome to IdleWheels!');
        redirect($this->returnTo());
    }

    public function logout(): void
    {
        Auth::logout();
        Session::start();
        flash('success', 'You have logged out.');
        redirect('/');
    }
}
