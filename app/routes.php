<?php
declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\BookingController;
use App\Controllers\CarController;
use App\Controllers\HomeController;
use App\Controllers\HostController;
use App\Controllers\PageController;
use App\Controllers\PaymentController;


// Public
$router->get('/', [HomeController::class, 'index']);
$router->get('/cars', [CarController::class, 'index']);
$router->get('/cars/{slug}', [CarController::class, 'show']);
$router->post('/cars/{slug}/save', [CarController::class, 'toggleSave']);

// Pages
$router->get('/how-it-works', [PageController::class, 'howItWorks']);
$router->get('/about', [PageController::class, 'about']);
$router->get('/faq', [PageController::class, 'faq']);
$router->get('/contact', [PageController::class, 'contact']);
$router->post('/contact', [PageController::class, 'sendContact']);
$router->get('/become-a-host', [PageController::class, 'becomeHost']);
$router->get('/terms', [PageController::class, 'terms']);
$router->get('/privacy', [PageController::class, 'privacy']);
$router->get('/rental-policy', [PageController::class, 'rentalPolicy']);
$router->get('/insurance-safety', [PageController::class, 'insuranceSafety']);

// Accounts
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/cars/{slug}/book', [BookingController::class, 'summary']);
$router->post('/cars/{slug}/book', [BookingController::class, 'create']);
$router->get('/bookings/{ref}', [BookingController::class, 'show']);
$router->post('/bookings/{ref}/pay', [BookingController::class, 'pay']);
$router->post('/bookings/{ref}/cancel', [BookingController::class, 'cancel']);
$router->post('/bookings/{ref}/review', [BookingController::class, 'review']);

// Paystack
$router->get('/payment/callback', [PaymentController::class, 'callback']);
$router->post('/payment/webhook', [PaymentController::class, 'webhook'], ['csrf' => false]);

// Renter dashboard
$router->get('/account', [AccountController::class, 'index']);
$router->post('/account/profile', [AccountController::class, 'updateProfile']);
$router->post('/account/password', [AccountController::class, 'updatePassword']);

// Host dashboard
$router->get('/host', [HostController::class, 'index']);
$router->get('/host/apply', [HostController::class, 'applyForm']);
$router->post('/host/apply', [HostController::class, 'apply']);
$router->get('/host/cars/new', [HostController::class, 'newCar']);
$router->post('/host/cars', [HostController::class, 'storeCar']);
$router->get('/host/cars/{id}/edit', [HostController::class, 'editCar']);
$router->post('/host/cars/{id}', [HostController::class, 'updateCar']);
$router->post('/host/cars/{id}/photos/{photo}/delete', [HostController::class, 'deletePhoto']);
$router->post('/host/cars/{id}/feature', [HostController::class, 'featureCar']);
$router->post('/host/bookings/{ref}/accept', [HostController::class, 'accept']);
$router->post('/host/bookings/{ref}/decline', [HostController::class, 'decline']);

// Admin
$router->get('/admin', [AdminController::class, 'index']);
$router->post('/admin/hosts/{id}', [AdminController::class, 'decideHost']);
$router->post('/admin/cars/{id}', [AdminController::class, 'decideCar']);
$router->post('/admin/cars/{id}/feature', [AdminController::class, 'featureCar']);
$router->post('/admin/bookings/{ref}', [AdminController::class, 'updateBooking']);
$router->post('/admin/users/{id}', [AdminController::class, 'updateUser']);
$router->post('/admin/categories', [AdminController::class, 'saveCategory']);
$router->post('/admin/categories/{id}/delete', [AdminController::class, 'deleteCategory']);
$router->post('/admin/addons', [AdminController::class, 'saveAddon']);
$router->post('/admin/reviews/{id}', [AdminController::class, 'toggleReview']);
$router->post('/admin/settings', [AdminController::class, 'saveSettings']);
$router->post('/admin/messages/{id}', [AdminController::class, 'handleMessage']);
