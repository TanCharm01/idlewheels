<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Paystack;
use App\Core\Session;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Payment;
use App\Models\Review;
use App\Services\Pricing;

class BookingController extends Controller
{
    private function readRequest(array $car): array
    {
        $from = (string) input('from');
        $to = (string) input('to');
        $back = '/cars/' . $car['slug'];
        $valid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to);
        if (!$valid || $to <= $from) {
            flash('error', 'Choose a pick-up date and a later return date.');
            redirect($back, ['from' => $from, 'to' => $to]);
        }
        if ($from < date('Y-m-d')) {
            flash('error', 'The pick-up date can\'t be in the past.');
            redirect($back);
        }
        $days = Pricing::days($from, $to);
        if ($days < (int) $car['min_days'] || $days > (int) $car['max_days']) {
            flash('error', "This car can be rented for {$car['min_days']} to {$car['max_days']} days.");
            redirect($back, ['from' => $from, 'to' => $to]);
        }
        $qty = [];
        foreach ((array) ($_REQUEST['addons'] ?? []) as $id => $q) {
            if (ctype_digit((string) $id) && is_numeric($q) && (int) $q > 0) {
                $qty[(int) $id] = (int) $q;
            }
        }
        return [$from, $to, $qty];
    }

    public function summary(string $slug): void
    {
        $car = Car::findLiveBySlug($slug);
        if (!$car) {
            abort(404);
        }
        [$from, $to, $qty] = $this->readRequest($car);
        if (!Car::isAvailable((int) $car['id'], $from, $to)) {
            flash('error', 'Sorry, this car is already booked for some of those days. Try other dates.');
            redirect('/cars/' . $slug);
        }
        $this->view('bookings/summary', [
            'title' => 'Booking summary',
            'car' => $car,
            'photos' => Car::photos((int) $car['id']),
            'from' => $from,
            'to' => $to,
            'quote' => Pricing::quote($car, $from, $to, $qty),
            'qty' => $qty,
            'paystackReady' => Paystack::configured(),
        ]);
    }

    public function create(string $slug): void
    {
        $user = Auth::requireLogin();
        $car = Car::findLiveBySlug($slug);
        if (!$car) {
            abort(404);
        }
        if ((int) $car['host_id'] === (int) $user['id']) {
            flash('error', 'You can\'t book your own car.');
            redirect('/cars/' . $slug);
        }
        [$from, $to, $qty] = $this->readRequest($car);
        $phone = (string) input('phone');
        if (!preg_match('/^[0-9+()\s-]{7,20}$/', $phone)) {
            flash('error', 'Enter a phone number the host can reach you on.');
            redirect('/cars/' . $slug . '/book', ['from' => $from, 'to' => $to, 'addons' => $qty]);
        }
        if (empty($_POST['agree'])) {
            flash('error', 'Please accept the rental policy to continue.');
            redirect('/cars/' . $slug . '/book', ['from' => $from, 'to' => $to, 'addons' => $qty]);
        }
        if (!Car::isAvailable((int) $car['id'], $from, $to)) {
            flash('error', 'Sorry, this car was just booked for some of those days. Try other dates.');
            redirect('/cars/' . $slug);
        }
        $quote = Pricing::quote($car, $from, $to, $qty);
        $booking = Booking::create($car, (int) $user['id'], $from, $to, $quote, $phone, mb_substr((string) input('note'), 0, 500));
        $this->startPayment(Booking::find($booking['id']), $user);
    }

    public function pay(string $ref): void
    {
        $user = Auth::requireLogin();
        $b = Booking::findByRef($ref);
        if (!$b || (int) $b['renter_id'] !== (int) $user['id']) {
            abort(404);
        }
        if ($b['status'] !== 'pending_payment') {
            redirect('/bookings/' . $ref);
        }
        if (!Car::isAvailable((int) $b['car_id'], $b['start_date'], $b['end_date']) && strtotime($b['created_at']) < time() - 1800) {
            flash('error', 'Those dates are no longer available. Please book again.');
            Booking::update((int) $b['id'], ['status' => 'cancelled']);
            redirect('/cars/' . $b['car_slug']);
        }
        $this->startPayment($b, $user);
    }

    private function startPayment(array $b, array $user): void
    {
        try {
            $reference = Paystack::newReference('BK');
            Payment::create($reference, 'booking', (int) $user['id'], (float) $b['total'], (int) $b['id'], null);
            $tx = Paystack::initialize($user['email'], (float) $b['total'], $reference, absolute_url('/payment/callback'), [
                'booking_ref' => $b['ref'],
                'car' => $b['car_title'],
            ]);
            redirect($tx['authorization_url']);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/bookings/' . $b['ref']);
        }
    }

    public function show(string $ref): void
    {
        $user = Auth::requireLogin();
        $b = Booking::findByRef($ref);
        $allowed = $b && ((int) $b['renter_id'] === (int) $user['id'] || (int) $b['host_id'] === (int) $user['id'] || Auth::isAdmin());
        if (!$allowed) {
            abort(404);
        }
        $this->view('bookings/show', [
            'title' => 'Booking ' . $b['ref'],
            'b' => $b,
            'addons' => Booking::addons((int) $b['id']),
            'isRenter' => (int) $b['renter_id'] === (int) $user['id'],
            'isHost' => (int) $b['host_id'] === (int) $user['id'],
        ]);
    }

    public function cancel(string $ref): void
    {
        $user = Auth::requireLogin();
        $b = Booking::findByRef($ref);
        if (!$b || (int) $b['renter_id'] !== (int) $user['id']) {
            abort(404);
        }
        if (!in_array($b['status'], ['pending_payment', 'awaiting_host', 'confirmed'], true) || $b['start_date'] <= date('Y-m-d')) {
            flash('error', 'This booking can no longer be cancelled online. Contact us for help.');
            redirect('/bookings/' . $ref);
        }
        Booking::update((int) $b['id'], [
            'status' => 'cancelled',
            'payment_status' => $b['payment_status'] === 'paid' ? 'refund_due' : $b['payment_status'],
        ]);
        flash('success', $b['payment_status'] === 'paid' ? 'Booking cancelled. Your refund will be processed according to the rental policy.' : 'Booking cancelled.');
        redirect('/bookings/' . $ref);
    }

    public function review(string $ref): void
    {
        $user = Auth::requireLogin();
        $b = Booking::findByRef($ref);
        if (!$b || (int) $b['renter_id'] !== (int) $user['id']) {
            abort(404);
        }
        if (!Booking::isCompleted($b) || $b['review_id']) {
            flash('error', 'You can review a car once your confirmed rental has ended, once per booking.');
            redirect('/bookings/' . $ref);
        }
        $data = $this->validate(['rating' => 'required|int|between:1,5', 'comment' => 'required|min:5|max:1500']);
        Review::create($b, (int) $data['rating'], $data['comment']);
        flash('success', 'Thanks for your review!');
        redirect('/bookings/' . $ref);
    }
}
