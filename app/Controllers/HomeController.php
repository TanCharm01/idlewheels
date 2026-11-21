<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Car;
use App\Models\Category;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home', [
            'title' => 'IdleWheels',
            'description' => 'Peer-to-peer car rental in Ghana: rent idle cars from local owners by the day, or list your own car.',
            'cars' => Car::featured(6),
            'categories' => Category::all(),
            'locations' => Car::locations(),
        ]);
    }
}
