<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$router = new App\Core\Router();
require __DIR__ . '/app/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', request_path());
