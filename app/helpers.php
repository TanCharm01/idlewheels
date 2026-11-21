<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;
use App\Models\Setting;

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

function config(string $key, $default = null)
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $configured = (string) config('app.url', '');
        if ($configured !== '') {
            $base = rtrim((string) parse_url($configured, PHP_URL_PATH), '/');
        } else {
            $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        }
    }
    return $base;
}

function url(string $path = '/', array $query = []): string
{
    // keep any ?query or #fragment that's already in the path
    $tail = '';
    if (preg_match('/^([^?#]*)([?#].*)$/', $path, $m)) {
        [$path, $tail] = [$m[1], $m[2]];
    }
    $path = '/' . ltrim($path, '/');
    if (config('app.php_urls', true)) {
        $path = php_page($path);
    }
    $u = base_path() . $path . $tail;
    if ($query) {
        $u .= (strpos($tail, '?') === false ? '?' : '&') . http_build_query($query);
    }
    return $u === '' ? '/' : $u;
}

// Page addresses end in .php (/cars -> /cars.php, / -> /index.php).
// Files such as assets/css/app.css or uploads/cars/x.jpg are left as they are.
function php_page(string $path): string
{
    if ($path === '/' || $path === '') {
        return '/index.php';
    }
    $path = rtrim($path, '/');
    $last = substr($path, strrpos($path, '/') + 1);
    return strpos($last, '.') === false ? $path . '.php' : $path;
}

function absolute_url(string $path = '/', array $query = []): string
{
    $configured = rtrim((string) config('app.url', ''), '/');
    if ($configured !== '') {
        $origin = preg_replace('#^(https?://[^/]+).*$#', '$1', $configured);
    } else {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $origin = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $origin . url($path, $query);
}

function asset(string $path): string
{
    $file = BASE_PATH . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . ltrim($path, '/')) . $v;
}

function photo_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    return preg_match('#^https?://#', $path) ? $path : url($path);
}

function request_path(): string
{
    $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $base = base_path();
    if ($base !== '' && strpos($path, $base) === 0) {
        $path = substr($path, strlen($base));
    }
    $path = '/' . trim($path, '/');
    if ($path === '/index.php') {
        return '/';
    }
    // /cars.php and /cars both reach the /cars page
    return substr($path, -4) === '.php' ? substr($path, 0, -4) : $path;
}

function redirect(string $path, array $query = []): void
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path, $query)));
    exit;
}

function back(string $fallback = '/'): void
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($ref && parse_url($ref, PHP_URL_HOST) === $host) {
        header('Location: ' . $ref);
        exit;
    }
    redirect($fallback);
}

function flash(string $type, string $message): void
{
    Session::flash($type, $message);
}

function old(string $key, $default = '')
{
    $old = Session::get('_old', []);
    return $old[$key] ?? $default;
}

function errors(): array
{
    return Session::get('_errors', []);
}

function error_for(string $key): string
{
    $errs = errors();
    return isset($errs[$key]) ? '<p class="mt-1 text-xs text-destructive">' . e($errs[$key]) . '</p>' : '';
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function currency(): string
{
    return Setting::get('currency', 'GHS');
}

function money($amount, ?string $cur = null): string
{
    $cur = $cur ?? currency();
    return $cur . ' ' . number_format((float) $amount, 2);
}

function money_short($amount): string
{
    $n = (float) $amount;
    return currency() . ' ' . (fmod($n, 1.0) == 0.0 ? number_format($n, 0) : number_format($n, 2));
}

function fmt_date(?string $date, string $format = 'D j M Y'): string
{
    return $date ? date($format, strtotime($date)) : '';
}

function slugify(string $text): string
{
    $text = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
    return $text !== '' ? $text : 'item';
}

function input(string $key, $default = '')
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function abort(int $status = 404, string $message = ''): void
{
    http_response_code($status);
    App\Core\View::render($status === 404 ? 'errors/404' : 'errors/error', ['title' => $status === 404 ? 'Page not found' : 'Something went wrong', 'message' => $message, 'status' => $status]);
    exit;
}

function booking_status_label(string $status): string
{
    $labels = [
        'pending_payment' => 'Awaiting payment',
        'awaiting_host' => 'Awaiting host',
        'confirmed' => 'Confirmed',
        'declined' => 'Declined',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
    ];
    return $labels[$status] ?? ucfirst($status);
}
