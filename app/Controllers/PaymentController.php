<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Paystack;
use App\Models\Booking;
use App\Models\Car;
use App\Models\FeaturedPurchase;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function callback(): void
    {
        $reference = (string) input('reference', input('trxref'));
        $payment = $reference !== '' ? Payment::findByReference($reference) : null;
        if (!$payment) {
            flash('error', 'We couldn\'t find that payment.');
            redirect('/account');
        }
        try {
            $ok = $this->fulfil($payment);
        } catch (\RuntimeException $e) {
            flash('error', 'We couldn\'t confirm your payment yet: ' . $e->getMessage());
            $ok = false;
        }
        if ($payment['purpose'] === 'featured') {
            flash($ok ? 'success' : 'error', $ok ? 'Payment received. Your car is now featured.' : 'The featured listing payment didn\'t go through.');
            redirect('/host', ['tab' => 'listings']);
        }
        $b = Booking::find((int) $payment['booking_id']);
        if (!$ok) {
            flash('error', 'Your payment didn\'t go through. You can try again from this page.');
        }
        redirect('/bookings/' . ($b['ref'] ?? ''));
    }

    public function webhook(): void
    {
        $body = (string) file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
        if (!Paystack::validSignature($body, $signature)) {
            http_response_code(401);
            exit;
        }
        $event = json_decode($body, true);
        if (($event['event'] ?? '') === 'charge.success') {
            $payment = Payment::findByReference((string) ($event['data']['reference'] ?? ''));
            if ($payment) {
                try {
                    $this->fulfil($payment);
                } catch (\Throwable $e) {
                    error_log('Paystack webhook: ' . $e->getMessage());
                }
            }
        }
        http_response_code(200);
        exit;
    }

    private function fulfil(array $payment): bool
    {
        if ($payment['status'] === 'success') {
            return true;
        }
        $tx = Paystack::verify($payment['reference']);
        $paid = $tx['status'] === 'success'
            && abs($tx['amount'] - (float) $payment['amount']) < 0.01
            && strtoupper($tx['currency']) === strtoupper($payment['currency']);
        if (!$paid) {
            if (in_array($tx['status'], ['failed', 'abandoned'], true)) {
                Payment::mark((int) $payment['id'], $tx['status'], $tx['channel']);
            }
            return false;
        }
        return (bool) Database::transaction(function () use ($payment, $tx) {
            $row = Database::one('SELECT * FROM iw_payments WHERE id = ? FOR UPDATE', [(int) $payment['id']]);
            if ($row['status'] === 'success') {
                return true;
            }
            Payment::mark((int) $payment['id'], 'success', $tx['channel'], $tx['paid_at']);
            if ($payment['purpose'] === 'booking') {
                $b = Booking::find((int) $payment['booking_id']);
                if ($b && $b['status'] === 'pending_payment') {
                    Booking::update((int) $b['id'], [
                        'payment_status' => 'paid',
                        'status' => $b['requires_approval'] ? 'awaiting_host' : 'confirmed',
                    ]);
                } elseif ($b && $b['status'] === 'cancelled') {
                    Booking::update((int) $b['id'], ['payment_status' => 'refund_due']);
                }
            } else {
                $fp = FeaturedPurchase::find((int) $payment['featured_purchase_id']);
                if ($fp && $fp['status'] === 'pending') {
                    [$start, $end] = Car::extendFeatured((int) $fp['car_id'], (int) $fp['days']);
                    FeaturedPurchase::markPaid((int) $fp['id'], $start, $end);
                }
            }
            return true;
        });
    }
}
