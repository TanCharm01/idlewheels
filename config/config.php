<?php
// Works out whether we're on local XAMPP or the live school server,
// then reads that environment's settings from .env (LOCAL_* or LIVE_*).
// Set APP_ENV=local or APP_ENV=live in .env to force one.
$appEnv = strtolower((string) env('APP_ENV', 'auto'));
if ($appEnv !== 'local' && $appEnv !== 'live') {
    $host = strtolower((string) ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host);
    // Command-line scripts (tests, make-admin) have no host, so they count as local.
    $isLocal = PHP_SAPI === 'cli' || in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true);
    $appEnv = $isLocal ? 'local' : 'live';
}
$p = $appEnv === 'local' ? 'LOCAL_' : 'LIVE_';

return [
    'app' => [
        'name' => 'IdleWheels',
        'env' => $appEnv,
        // Leave the URL empty to work it out from the request (works in any folder).
        'url' => (string) env($p . 'APP_URL', ''),
        'debug' => (bool) env($p . 'APP_DEBUG', $appEnv === 'local'),
        // Links end in .php (/cars.php). Set PHP_URLS=false in .env for clean links; both kinds always work.
        'php_urls' => (bool) env('PHP_URLS', true),
        'timezone' => 'Africa/Accra',
    ],
    'db' => [
        'host' => (string) env($p . 'DB_HOST', 'localhost'),
        'port' => (int) env($p . 'DB_PORT', $appEnv === 'local' ? 3307 : 3306),
        'name' => (string) env($p . 'DB_NAME', 'idlewheels'),
        'user' => (string) env($p . 'DB_USER', 'root'),
        'pass' => (string) env($p . 'DB_PASS', ''),
    ],
    'paystack' => [
        'public_key' => (string) env('PAYSTACK_PUBLIC_KEY', ''),
        'secret_key' => (string) env('PAYSTACK_SECRET_KEY', ''),
        // Payment methods offered on Paystack's checkout page.
        'channels' => ['card', 'mobile_money'],
    ],
    'mail' => [
        'admin_email' => (string) env('MAIL_ADMIN_EMAIL', ''),
        'from' => (string) env('MAIL_FROM', 'no-reply@idlewheels.local'),
    ],
];
