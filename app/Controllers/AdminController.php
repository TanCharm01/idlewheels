<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Upload;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Category;
use App\Models\Content;
use App\Models\FeaturedPurchase;
use App\Models\HostProfile;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;

class AdminController extends Controller
{
    private const TABS = ['overview', 'hosts', 'cars', 'bookings', 'users', 'revenue', 'addons', 'categories', 'reviews', 'messages', 'settings'];

    public function index(): void
    {
        $admin = Auth::requireAdmin();
        $tab = (string) input('tab', 'overview');
        if (!in_array($tab, self::TABS, true)) {
            $tab = 'overview';
        }
        $data = ['title' => 'Admin', 'tab' => $tab, 'admin' => $admin];
        switch ($tab) {
            case 'overview':
                $data['stats'] = $this->stats();
                break;
            case 'hosts':
                $data['hosts'] = HostProfile::allForAdmin();
                break;
            case 'cars':
                $data['filter'] = (string) input('status', 'pending');
                $data['cars'] = Car::allForAdmin($data['filter'] === 'all' ? '' : $data['filter']);
                break;
            case 'bookings':
                $data['filter'] = (string) input('filter', 'upcoming');
                $data['bookings'] = Booking::allForAdmin($data['filter'] === 'all' ? '' : $data['filter']);
                break;
            case 'users':
                $data['q'] = (string) input('q');
                $data['users'] = User::allForAdmin($data['q']);
                break;
            case 'revenue':
                $data['revenue'] = $this->revenue((int) input('days', 90));
                break;
            case 'addons':
                $data['addons'] = Addon::all();
                break;
            case 'categories':
                $data['categories'] = Category::all();
                break;
            case 'reviews':
                $data['reviews'] = Review::allForAdmin();
                break;
            case 'messages':
                $data['messages'] = Content::messages();
                break;
            case 'settings':
                $data['settings'] = Setting::all();
                break;
        }
        $this->view('admin/index', $data);
    }

    private function stats(): array
    {
        return [
            'pending_hosts' => (int) Database::value("SELECT COUNT(*) FROM iw_host_profiles WHERE status = 'pending'"),
            'pending_cars' => (int) Database::value("SELECT COUNT(*) FROM iw_cars WHERE status = 'pending'"),
            'live_cars' => (int) Database::value("SELECT COUNT(*) FROM iw_cars WHERE status = 'approved'"),
            'awaiting_host' => (int) Database::value("SELECT COUNT(*) FROM iw_bookings WHERE status = 'awaiting_host'"),
            'upcoming' => (int) Database::value("SELECT COUNT(*) FROM iw_bookings WHERE status = 'confirmed' AND end_date >= CURDATE()"),
            'refunds' => (int) Database::value("SELECT COUNT(*) FROM iw_bookings WHERE payment_status = 'refund_due'"),
            'messages' => (int) Database::value('SELECT COUNT(*) FROM iw_contact_messages WHERE handled = 0'),
            'users' => (int) Database::value("SELECT COUNT(*) FROM iw_users WHERE password_hash <> '!'"),
        ];
    }

    private function revenue(int $days): array
    {
        $days = in_array($days, [7, 30, 90, 365], true) ? $days : 90;
        $since = date('Y-m-d', strtotime("-$days days"));
        $rows = Database::all(
            "SELECT b.ref, b.start_date, b.end_date, b.status, b.rental_amount, b.addons_amount, b.service_fee, b.commission, b.host_earnings, b.total, c.title AS car_title
             FROM iw_bookings b JOIN iw_cars c ON c.id = b.car_id
             WHERE b.payment_status = 'paid' AND b.status IN ('confirmed','completed','awaiting_host') AND b.created_at >= ?
             ORDER BY b.created_at DESC",
            [$since]
        );
        $featured = Database::one(
            "SELECT COUNT(*) n, COALESCE(SUM(amount), 0) total FROM iw_featured_purchases WHERE status = 'paid' AND source = 'purchase' AND created_at >= ?",
            [$since]
        );
        $sum = function (string $k) use ($rows) {
            return round(array_sum(array_map(function ($r) use ($k) { return (float) $r[$k]; }, $rows)), 2);
        };
        $commission = $sum('commission');
        $fees = $sum('service_fee');
        $addons = $sum('addons_amount');
        return [
            'days' => $days,
            'rows' => $rows,
            'bookings' => count($rows),
            'gross' => $sum('total'),
            'commission' => $commission,
            'service_fees' => $fees,
            'addons' => $addons,
            'host_earnings' => $sum('host_earnings'),
            'featured' => (float) $featured['total'],
            'featured_count' => (int) $featured['n'],
            'platform' => round($commission + $fees + (float) $featured['total'], 2),
        ];
    }

    public function decideHost(string $id): void
    {
        Auth::requireAdmin();
        $status = (string) input('status');
        if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
            abort(400);
        }
        $note = mb_substr((string) input('note'), 0, 500);
        if ($status === 'rejected' && $note === '') {
            flash('error', 'Add a reason so the applicant knows what to fix.');
            redirect('/admin', ['tab' => 'hosts']);
        }
        HostProfile::setStatus((int) $id, $status, $note);
        flash('success', 'Host application ' . $status . '.');
        redirect('/admin', ['tab' => 'hosts']);
    }

    public function decideCar(string $id): void
    {
        Auth::requireAdmin();
        $car = Car::find((int) $id);
        if (!$car) {
            abort(404);
        }
        $status = (string) input('status');
        if (!in_array($status, ['approved', 'rejected', 'hidden', 'pending'], true)) {
            abort(400);
        }
        $note = mb_substr((string) input('note'), 0, 500);
        if ($status === 'rejected' && $note === '') {
            flash('error', 'Add a reason so the host knows what to fix.');
            redirect('/admin', ['tab' => 'cars', 'status' => $car['status']]);
        }
        if ($status === 'approved') {
            $host = HostProfile::forUser((int) $car['host_id']);
            if (!$host || $host['status'] !== 'approved') {
                flash('error', 'Approve the host before their car.');
                redirect('/admin', ['tab' => 'cars', 'status' => $car['status']]);
            }
        }
        Car::setStatus((int) $car['id'], $status, $note);
        flash('success', $car['title'] . ($status === 'approved' ? ' is now live.' : ' updated.'));
        redirect('/admin', ['tab' => 'cars', 'status' => $car['status']]);
    }

    public function featureCar(string $id): void
    {
        Auth::requireAdmin();
        $car = Car::find((int) $id);
        if (!$car || $car['status'] !== 'approved') {
            flash('error', 'Only live cars can be featured.');
            redirect('/admin', ['tab' => 'cars', 'status' => 'approved']);
        }
        if (input('action') === 'end') {
            Car::endFeatured((int) $car['id']);
            flash('success', 'Featuring ended for ' . $car['title'] . '.');
        } else {
            $days = max(1, min(90, (int) input('days', 7)));
            [$start, $end] = Car::extendFeatured((int) $car['id'], $days);
            $fp = FeaturedPurchase::create((int) $car['id'], (int) $car['host_id'], $days, 0, 'admin', 'paid');
            FeaturedPurchase::markPaid($fp, $start, $end);
            flash('success', $car['title'] . ' is featured until ' . fmt_date($end, 'j M Y') . '.');
        }
        redirect('/admin', ['tab' => 'cars', 'status' => 'approved']);
    }

    public function updateBooking(string $ref): void
    {
        Auth::requireAdmin();
        $b = Booking::findByRef($ref);
        if (!$b) {
            abort(404);
        }
        $action = (string) input('action');
        switch ($action) {
            case 'confirm':
                if ($b['status'] === 'awaiting_host') {
                    Booking::update((int) $b['id'], ['status' => 'confirmed']);
                }
                break;
            case 'decline':
                if ($b['status'] === 'awaiting_host') {
                    Booking::update((int) $b['id'], ['status' => 'declined', 'payment_status' => $b['payment_status'] === 'paid' ? 'refund_due' : $b['payment_status']]);
                }
                break;
            case 'cancel':
                if (in_array($b['status'], ['pending_payment', 'awaiting_host', 'confirmed'], true)) {
                    Booking::update((int) $b['id'], ['status' => 'cancelled', 'payment_status' => $b['payment_status'] === 'paid' ? 'refund_due' : $b['payment_status']]);
                }
                break;
            case 'refunded':
                if ($b['payment_status'] === 'refund_due') {
                    Booking::update((int) $b['id'], ['payment_status' => 'refunded']);
                }
                break;
            default:
                abort(400);
        }
        flash('success', 'Booking ' . $b['ref'] . ' updated.');
        redirect('/admin', ['tab' => 'bookings', 'filter' => (string) input('filter', 'upcoming')]);
    }

    public function updateUser(string $id): void
    {
        $admin = Auth::requireAdmin();
        if ((int) $id === (int) $admin['id']) {
            flash('error', 'You can\'t change your own account here.');
            redirect('/admin', ['tab' => 'users']);
        }
        $action = (string) input('action');
        if ($action === 'block' || $action === 'unblock') {
            User::setStatus((int) $id, $action === 'block' ? 'blocked' : 'active');
        } elseif ($action === 'make_admin' || $action === 'remove_admin') {
            User::setRole((int) $id, $action === 'make_admin' ? 'admin' : 'user');
        } else {
            abort(400);
        }
        flash('success', 'User updated.');
        redirect('/admin', ['tab' => 'users']);
    }

    public function saveCategory(): void
    {
        Auth::requireAdmin();
        $d = $this->validate(['name' => 'required|max:60', 'blurb' => 'max:150', 'sort_order' => 'int'], '/admin?tab=categories');
        $id = (int) input('id') ?: null;
        $data = ['name' => $d['name'], 'slug' => slugify($d['name']), 'blurb' => $d['blurb'] ?: null, 'sort_order' => (int) ($d['sort_order'] ?? 0)];
        try {
            $img = Upload::images('image', 'categories', 1);
            if ($img) {
                $data['image'] = $img[0];
            }
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin', ['tab' => 'categories']);
        }
        Category::save($id, $data);
        flash('success', 'Category saved.');
        redirect('/admin', ['tab' => 'categories']);
    }

    public function deleteCategory(string $id): void
    {
        Auth::requireAdmin();
        Category::delete((int) $id);
        flash('success', 'Category deleted. Its cars keep their listings without a category.');
        redirect('/admin', ['tab' => 'categories']);
    }

    public function saveAddon(): void
    {
        Auth::requireAdmin();
        $d = $this->validate([
            'name' => 'required|max:100',
            'description' => 'max:200',
            'price' => 'required|numeric|between:0,100000',
            'max_quantity' => 'required|int|between:1,60',
            'sort_order' => 'int',
        ], '/admin?tab=addons');
        $id = (int) input('id') ?: null;
        Addon::save($id, [
            'name' => $d['name'],
            'description' => $d['description'] ?: null,
            'price' => $d['price'],
            'per_day' => empty($_POST['per_day']) ? 0 : 1,
            'max_quantity' => $d['max_quantity'],
            'active' => empty($_POST['active']) ? 0 : 1,
            'sort_order' => (int) ($d['sort_order'] ?? 0),
        ]);
        flash('success', 'Extra saved.');
        redirect('/admin', ['tab' => 'addons']);
    }

    public function toggleReview(string $id): void
    {
        Auth::requireAdmin();
        Review::setHidden((int) $id, input('action') === 'hide');
        flash('success', 'Review updated.');
        redirect('/admin', ['tab' => 'reviews']);
    }

    public function saveSettings(): void
    {
        Auth::requireAdmin();
        $d = $this->validate([
            'commission_percent' => 'required|numeric|between:0,50',
            'service_fee_percent' => 'required|numeric|between:0,50',
            'service_fee_name' => 'required|max:60',
            'featured_price' => 'required|numeric|between:0,100000',
            'featured_days' => 'required|int|between:1,90',
        ], '/admin?tab=settings');
        foreach ($d as $k => $v) {
            Setting::set($k, (string) $v);
        }
        flash('success', 'Settings saved. New bookings use the new rates.');
        redirect('/admin', ['tab' => 'settings']);
    }

    public function handleMessage(string $id): void
    {
        Auth::requireAdmin();
        Content::markHandled((int) $id);
        redirect('/admin', ['tab' => 'messages']);
    }
}
