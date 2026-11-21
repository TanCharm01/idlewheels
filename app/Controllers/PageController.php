<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Car;
use App\Models\Content;
use App\Models\Setting;

class PageController extends Controller
{
    public function howItWorks(): void
    {
        $this->view('pages/how-it-works', ['title' => 'How it works', 'description' => 'How renting and hosting a car on IdleWheels works.']);
    }

    public function about(): void
    {
        $this->view('pages/about', ['title' => 'About us', 'description' => 'IdleWheels connects owners of idle cars with people who need one.']);
    }

    public function faq(): void
    {
        $this->view('pages/faq', ['title' => 'FAQ', 'description' => 'Answers about renting, payments and hosting on IdleWheels.', 'faqs' => Content::faqs()]);
    }

    public function becomeHost(): void
    {
        $this->view('pages/become-host', [
            'title' => 'Become a host',
            'description' => 'Rent out your car when you\'re not using it. Apply to host on IdleWheels.',
            'commission' => Setting::num('commission_percent', 15),
            'featuredPrice' => Setting::num('featured_price', 50),
            'featuredDays' => (int) Setting::num('featured_days', 7),
            'photo' => Car::featured(1)[0]['photo'] ?? '',
        ]);
    }

    public function contact(): void
    {
        $this->view('pages/contact', ['title' => 'Contact', 'description' => 'Questions about renting or hosting a car on IdleWheels? Send us a message.']);
    }

    public function sendContact(): void
    {
        $d = $this->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|max:190',
            'phone' => 'phone|max:30',
            'topic' => 'required|in:Renter,Car owner,Something else',
            'booking_ref' => 'max:20',
            'message' => 'required|min:10|max:3000',
        ], '/contact');
        // A filled hidden field means a bot.
        if (!empty($_POST['website'])) {
            redirect('/contact');
        }
        Content::saveMessage([
            'name' => $d['name'],
            'email' => $d['email'],
            'phone' => $d['phone'] ?: null,
            'topic' => $d['topic'],
            'booking_ref' => $d['booking_ref'] ?: null,
            'message' => $d['message'],
        ]);
        $to = (string) config('mail.admin_email', '');
        if ($to !== '' && function_exists('mail')) {
            @mail($to, 'IdleWheels contact: ' . $d['topic'], $d['message'] . "\n\nFrom: {$d['name']} <{$d['email']}>", 'From: ' . config('mail.from'));
        }
        flash('success', 'Thanks for getting in touch. We\'ll reply by email as soon as we can.');
        redirect('/contact');
    }

    public function terms(): void
    {
        $this->view('pages/legal/terms', ['title' => 'Terms & Conditions']);
    }

    public function privacy(): void
    {
        $this->view('pages/legal/privacy', ['title' => 'Privacy Policy']);
    }

    public function rentalPolicy(): void
    {
        $this->view('pages/legal/rental-policy', ['title' => 'Rental & cancellation policy']);
    }

    public function insuranceSafety(): void
    {
        $this->view('pages/legal/insurance-safety', ['title' => 'Insurance & safety']);
    }
}
