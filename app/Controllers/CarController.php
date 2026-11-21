<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Addon;
use App\Models\Car;
use App\Models\Category;
use App\Models\Review;
use App\Models\SavedCar;

class CarController extends Controller
{
    public function index(): void
    {
        $f = [
            'q' => (string) input('q'),
            'location' => (string) input('location'),
            'from' => (string) input('from'),
            'to' => (string) input('to'),
            'type' => (string) input('type'),
            'seats' => (string) input('seats'),
            'transmission' => (string) input('transmission'),
            'max_price' => (string) input('max_price'),
            'sort' => (string) input('sort'),
        ];
        // Ignore nonsense dates instead of failing the search.
        foreach (['from', 'to'] as $k) {
            if ($f[$k] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $f[$k])) {
                $f[$k] = '';
            }
        }
        if ($f['from'] !== '' && $f['to'] !== '' && $f['to'] <= $f['from']) {
            $f['to'] = '';
        }
        $this->view('cars/index', [
            'title' => 'Browse cars',
            'description' => 'Rent cars from local owners by the day. Filter by location, dates, type, seats, transmission and price.',
            'cars' => Car::search($f),
            'filters' => $f,
            'categories' => Category::all(),
            'locations' => Car::locations(),
        ]);
    }

    public function show(string $slug): void
    {
        $car = Car::findLiveBySlug($slug);
        if (!$car) {
            abort(404);
        }
        $user = Auth::user();
        $this->view('cars/show', [
            'title' => $car['title'],
            'description' => $car['tagline'] ?: mb_substr($car['description'], 0, 150),
            'car' => $car,
            'photos' => Car::photos((int) $car['id']),
            'reviews' => Review::forCar((int) $car['id']),
            'held' => Car::heldRanges((int) $car['id']),
            'addons' => Addon::active(),
            'saved' => $user ? SavedCar::isSaved((int) $user['id'], (int) $car['id']) : false,
            'from' => (string) input('from'),
            'to' => (string) input('to'),
        ]);
    }

    public function toggleSave(string $slug): void
    {
        $user = Auth::requireLogin();
        $car = Car::findLiveBySlug($slug);
        if (!$car) {
            abort(404);
        }
        $saved = SavedCar::toggle((int) $user['id'], (int) $car['id']);
        flash('success', $saved ? 'Saved to your account.' : 'Removed from your saved cars.');
        back('/cars/' . $slug);
    }
}
