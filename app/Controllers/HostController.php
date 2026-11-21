<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Paystack;
use App\Core\Session;
use App\Core\Upload;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Category;
use App\Models\FeaturedPurchase;
use App\Models\HostProfile;
use App\Models\Payment;
use App\Models\Setting;

class HostController extends Controller
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        $profile = HostProfile::forUser((int) $user['id']);
        $tab = (string) input('tab', 'overview');
        if (!in_array($tab, ['overview', 'listings', 'requests', 'earnings'], true)) {
            $tab = 'overview';
        }
        $cars = $profile ? Car::forHost((int) $user['id']) : [];
        $bookings = ($profile && $profile['status'] === 'approved') ? Booking::forHost((int) $user['id']) : [];
        $this->view('host/index', [
            'title' => 'Host dashboard',
            'tab' => $tab,
            'profile' => $profile,
            'cars' => $cars,
            'bookings' => $bookings,
            'featuredPrice' => Setting::num('featured_price', 50),
            'featuredDays' => (int) Setting::num('featured_days', 7),
            'commissionPercent' => Setting::num('commission_percent', 15),
            'paystackReady' => Paystack::configured(),
        ]);
    }

    public function applyForm(): void
    {
        $user = Auth::requireLogin();
        $this->view('host/apply', ['title' => 'Apply to host', 'profile' => HostProfile::forUser((int) $user['id']), 'user' => $user]);
    }

    public function apply(): void
    {
        $user = Auth::requireLogin();
        $existing = HostProfile::forUser((int) $user['id']);
        if ($existing && $existing['status'] === 'approved') {
            flash('info', 'You\'re already an approved host, so there\'s no need to apply again.');
            redirect('/host');
        }
        $data = $this->validate([
            'display_name' => 'required|min:2|max:100',
            'phone' => 'required|phone',
            'city' => 'required|max:100',
            'payout_method' => 'required|max:150',
            'about' => 'max:1000',
        ]);
        if (empty($_POST['agree'])) {
            Session::withInput($_POST, ['agree' => 'Please confirm the hosting requirements.']);
            back('/host/apply');
        }
        HostProfile::apply((int) $user['id'], $data);
        flash('success', 'Application received. We\'ll review it and let you know, usually within two working days.');
        redirect('/host');
    }

    private function carRules(): array
    {
        return [
            'make' => 'required|max:60',
            'model' => 'required|max:60',
            'year' => 'required|int|between:1990,' . ((int) date('Y') + 1),
            'category_id' => 'required|int',
            'seats' => 'required|int|between:2,15',
            'transmission' => 'required|in:Automatic,Manual',
            'fuel' => 'required|in:Petrol,Diesel,Hybrid,Electric',
            'location' => 'required|max:120',
            'price_per_day' => 'required|numeric|between:5,10000',
            'min_days' => 'required|int|between:1,30',
            'max_days' => 'required|int|between:1,60',
            'tagline' => 'max:160',
            'description' => 'required|min:20|max:3000',
        ];
    }

    private function carData(array $d): array
    {
        $features = array_slice(array_filter(array_map('trim', (array) ($_POST['features'] ?? []))), 0, 12);
        $extra = trim((string) input('features_other'));
        if ($extra !== '') {
            $features = array_merge($features, array_filter(array_map('trim', explode(',', $extra))));
        }
        return [
            'title' => "{$d['year']} {$d['make']} {$d['model']}",
            'make' => $d['make'],
            'model' => $d['model'],
            'year' => $d['year'],
            'category_id' => Category::find((int) $d['category_id']) ? $d['category_id'] : null,
            'seats' => $d['seats'],
            'transmission' => $d['transmission'],
            'fuel' => $d['fuel'],
            'location' => $d['location'],
            'price_per_day' => $d['price_per_day'],
            'min_days' => min($d['min_days'], $d['max_days']),
            'max_days' => max($d['min_days'], $d['max_days']),
            'requires_approval' => empty($_POST['requires_approval']) ? 0 : 1,
            'tagline' => $d['tagline'] ?: null,
            'description' => $d['description'],
            'features' => mb_substr(implode(',', array_unique($features)), 0, 600) ?: null,
        ];
    }

    public function newCar(): void
    {
        Auth::requireApprovedHost();
        $this->view('host/car_form', ['title' => 'Add a car', 'car' => null, 'photos' => [], 'categories' => Category::all()]);
    }

    public function storeCar(): void
    {
        $user = Auth::user();
        Auth::requireApprovedHost();
        $d = $this->validate($this->carRules());
        try {
            $photos = Upload::images('photos', 'cars', 5);
        } catch (\RuntimeException $e) {
            Session::withInput($_POST, ['photos' => $e->getMessage()]);
            back('/host/cars/new');
        }
        if (!$photos) {
            Session::withInput($_POST, ['photos' => 'Add at least one photo of the car.']);
            back('/host/cars/new');
        }
        $data = $this->carData($d);
        $data += ['host_id' => (int) $user['id'], 'slug' => Car::uniqueSlug($data['title']), 'status' => 'pending'];
        Car::create($data, $photos);
        flash('success', 'Car submitted. The IdleWheels team will review it before it goes live.');
        redirect('/host', ['tab' => 'listings']);
    }

    private function ownCar(int $id): array
    {
        $user = Auth::user();
        $car = Car::find($id);
        if (!$car || (int) $car['host_id'] !== (int) $user['id']) {
            abort(404);
        }
        return $car;
    }

    public function editCar(string $id): void
    {
        Auth::requireApprovedHost();
        $car = $this->ownCar((int) $id);
        $this->view('host/car_form', ['title' => 'Edit ' . $car['title'], 'car' => $car, 'photos' => Car::photos((int) $car['id']), 'categories' => Category::all()]);
    }

    public function updateCar(string $id): void
    {
        Auth::requireApprovedHost();
        $car = $this->ownCar((int) $id);
        $d = $this->validate($this->carRules(), '/host/cars/' . $car['id'] . '/edit');
        try {
            $photos = Upload::images('photos', 'cars', max(0, 5 - count(Car::photos((int) $car['id']))));
        } catch (\RuntimeException $e) {
            Session::withInput($_POST, ['photos' => $e->getMessage()]);
            redirect('/host/cars/' . $car['id'] . '/edit');
        }
        $data = $this->carData($d);
        $data['slug'] = Car::uniqueSlug($data['title'], (int) $car['id']);
        // A rejected car goes back into review after it's edited.
        if ($car['status'] === 'rejected') {
            $data['status'] = 'pending';
        }
        Car::updateCar((int) $car['id'], $data);
        if ($photos) {
            Car::addPhotos((int) $car['id'], $photos);
        }
        flash('success', 'Car updated.');
        redirect('/host', ['tab' => 'listings']);
    }

    public function deletePhoto(string $id, string $photo): void
    {
        Auth::requireApprovedHost();
        $car = $this->ownCar((int) $id);
        if (count(Car::photos((int) $car['id'])) <= 1) {
            flash('error', 'A car needs at least one photo.');
            redirect('/host/cars/' . $car['id'] . '/edit');
        }
        $path = Car::removePhoto((int) $car['id'], (int) $photo);
        if ($path) {
            Upload::delete($path);
        }
        redirect('/host/cars/' . $car['id'] . '/edit');
    }

    public function featureCar(string $id): void
    {
        $user = Auth::user();
        Auth::requireApprovedHost();
        $car = $this->ownCar((int) $id);
        if ($car['status'] !== 'approved') {
            flash('error', 'Only live cars can be featured.');
            redirect('/host', ['tab' => 'listings']);
        }
        $price = Setting::num('featured_price', 50);
        $days = (int) Setting::num('featured_days', 7);
        try {
            $fpId = FeaturedPurchase::create((int) $car['id'], (int) $user['id'], $days, $price);
            $reference = Paystack::newReference('FT');
            Payment::create($reference, 'featured', (int) $user['id'], $price, null, $fpId);
            $tx = Paystack::initialize($user['email'], $price, $reference, absolute_url('/payment/callback'), ['featured_car' => $car['title'], 'days' => $days]);
            redirect($tx['authorization_url']);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/host', ['tab' => 'listings']);
        }
    }

    private function ownBooking(string $ref): array
    {
        $user = Auth::user();
        $b = Booking::findByRef($ref);
        if (!$b || (int) $b['host_id'] !== (int) $user['id']) {
            abort(404);
        }
        return $b;
    }

    public function accept(string $ref): void
    {
        Auth::requireApprovedHost();
        $b = $this->ownBooking($ref);
        if ($b['status'] !== 'awaiting_host') {
            flash('error', 'This request has already been answered.');
            redirect('/host', ['tab' => 'requests']);
        }
        Booking::update((int) $b['id'], ['status' => 'confirmed', 'host_note' => mb_substr((string) input('note'), 0, 500) ?: null]);
        flash('success', 'Booking ' . $b['ref'] . ' confirmed. Contact the renter to arrange the hand-over.');
        redirect('/host', ['tab' => 'requests']);
    }

    public function decline(string $ref): void
    {
        Auth::requireApprovedHost();
        $b = $this->ownBooking($ref);
        if ($b['status'] !== 'awaiting_host') {
            flash('error', 'This request has already been answered.');
            redirect('/host', ['tab' => 'requests']);
        }
        Booking::update((int) $b['id'], [
            'status' => 'declined',
            'payment_status' => $b['payment_status'] === 'paid' ? 'refund_due' : $b['payment_status'],
            'host_note' => mb_substr((string) input('note'), 0, 500) ?: null,
        ]);
        flash('success', 'Booking ' . $b['ref'] . ' declined. The renter will be refunded.');
        redirect('/host', ['tab' => 'requests']);
    }
}
