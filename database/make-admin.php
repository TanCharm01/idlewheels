<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require __DIR__ . '/../app/bootstrap.php';

$email = strtolower(trim($argv[1] ?? ''));
if ($email === '') {
    fwrite(STDERR, "Usage: php database/make-admin.php email@example.com\n");
    exit(1);
}
$n = App\Core\Database::run("UPDATE iw_users SET role = 'admin' WHERE email = ?", [$email]);
echo $n ? "$email is now an admin.\n" : "No account found for $email. Register on the site first.\n";
exit($n ? 0 : 1);
