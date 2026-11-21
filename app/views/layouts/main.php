<?php
use App\Core\Auth;
use App\Core\Session;

$user = Auth::user();
$path = request_path();
$nav = [['/cars', 'Browse cars'], ['/how-it-works', 'How it works'], ['/become-a-host', 'Become a host']];
$navClass = function ($href) use ($path) {
    return 'no-underline transition-colors hover:text-primary ' . (strpos($path, $href) === 0 ? 'text-primary font-semibold' : 'text-foreground');
};
$pageTitle = isset($title) && $title !== 'IdleWheels' ? $title . ' · IdleWheels' : 'IdleWheels';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <?php if (!empty($description)): ?><meta name="description" content="<?= e($description) ?>"><?php endif; ?>
  <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Wix+Madefor+Text:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&display=swap">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="min-h-screen">
  <header class="sticky top-0 z-40 border-b border-border/70 bg-background/90 backdrop-blur">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-8 lg:px-12">
      <a href="<?= url('/') ?>" class="flex items-center gap-2 text-foreground no-underline" aria-label="IdleWheels home">
        <svg width="26" height="26" viewBox="0 0 32 32" aria-hidden="true" class="text-primary"><circle cx="16" cy="16" r="13" fill="none" stroke="currentColor" stroke-width="3.5"/><circle cx="16" cy="16" r="3.5" fill="currentColor"/><path d="M16 3v9M16 20v9M3 16h9M20 16h9" stroke="currentColor" stroke-width="2"/></svg>
        <span class="font-display text-lg font-extrabold tracking-tight">IdleWheels</span>
      </a>
      <nav class="hidden items-center gap-7 text-sm md:flex" aria-label="Main">
        <?php foreach ($nav as [$href, $label]): ?>
          <a href="<?= url($href) ?>" class="<?= $navClass($href) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="flex items-center gap-2">
        <?php if ($user): ?>
          <details class="relative">
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-control border border-border bg-background py-1 pl-1 pr-3 text-sm font-semibold">
              <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs text-primary-foreground"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
              <span class="hidden max-w-[8rem] truncate sm:inline"><?= e($user['name']) ?></span>
            </summary>
            <div class="absolute right-0 top-full z-50 mt-2 w-52 overflow-hidden rounded-md border border-border bg-background py-1 shadow-lg">
              <a href="<?= url('/account') ?>" class="menu-item">My account</a>
              <a href="<?= url('/account', ['tab' => 'saved']) ?>" class="menu-item">Saved cars</a>
              <a href="<?= url('/host') ?>" class="menu-item">Host dashboard</a>
              <?php if ($user['role'] === 'admin'): ?><a href="<?= url('/admin') ?>" class="menu-item font-semibold text-primary">Admin</a><?php endif; ?>
              <form method="post" action="<?= url('/logout') ?>" class="border-t border-border"><?= csrf_field() ?><button class="menu-item w-full text-left">Log out</button></form>
            </div>
          </details>
        <?php else: ?>
          <a href="<?= url('/login', ['return' => $path]) ?>" class="rounded-control bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground no-underline hover:opacity-90">Log in</a>
        <?php endif; ?>
        <details class="relative md:hidden">
          <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center" aria-label="Menu">
            <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </summary>
          <nav class="absolute right-0 top-full z-50 mt-2 w-56 rounded-md border border-border bg-background py-2 shadow-lg" aria-label="Main">
            <?php foreach ($nav as [$href, $label]): ?><a href="<?= url($href) ?>" class="menu-item"><?= e($label) ?></a><?php endforeach; ?>
            <a href="<?= url('/faq') ?>" class="menu-item">FAQ</a>
            <a href="<?= url('/contact') ?>" class="menu-item">Contact</a>
          </nav>
        </details>
      </div>
    </div>
  </header>

  <?php $flashes = Session::flashes(); if ($flashes): ?>
    <div class="space-y-2 px-4 pt-4 sm:px-8 lg:px-12" role="status">
      <?php foreach ($flashes as $f): ?>
        <p class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <main><?= $content ?></main>

  <footer class="mt-24 bg-ink px-4 py-14 text-ink-foreground sm:px-8 lg:px-12">
    <div class="grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,5fr)]">
      <div class="max-w-sm">
        <p class="font-display text-xl font-extrabold tracking-tight">IdleWheels</p>
        <p class="mt-3 text-sm leading-relaxed text-ink-foreground/75">A marketplace for renting idle cars from their owners in Ghana, by the day.</p>
      </div>
      <div class="grid grid-cols-2 gap-10 text-sm sm:grid-cols-4">
        <div><p class="mb-3 font-semibold">Renters</p><ul class="space-y-2 text-ink-foreground/75">
          <li><a href="<?= url('/cars') ?>" class="footer-link">Browse cars</a></li>
          <li><a href="<?= url('/how-it-works') ?>" class="footer-link">How it works</a></li>
          <li><a href="<?= url('/account') ?>" class="footer-link">My account</a></li></ul></div>
        <div><p class="mb-3 font-semibold">Hosts</p><ul class="space-y-2 text-ink-foreground/75">
          <li><a href="<?= url('/become-a-host') ?>" class="footer-link">Become a host</a></li>
          <li><a href="<?= url('/host') ?>" class="footer-link">Host dashboard</a></li></ul></div>
        <div><p class="mb-3 font-semibold">IdleWheels</p><ul class="space-y-2 text-ink-foreground/75">
          <li><a href="<?= url('/about') ?>" class="footer-link">About us</a></li>
          <li><a href="<?= url('/contact') ?>" class="footer-link">Contact</a></li>
          <li><a href="<?= url('/faq') ?>" class="footer-link">FAQ</a></li></ul></div>
        <div><p class="mb-3 font-semibold">Policies</p><ul class="space-y-2 text-ink-foreground/75">
          <li><a href="<?= url('/terms') ?>" class="footer-link">Terms &amp; Conditions</a></li>
          <li><a href="<?= url('/privacy') ?>" class="footer-link">Privacy Policy</a></li>
          <li><a href="<?= url('/rental-policy') ?>" class="footer-link">Rental &amp; cancellation policy</a></li>
          <li><a href="<?= url('/insurance-safety') ?>" class="footer-link">Insurance &amp; safety</a></li></ul></div>
      </div>
    </div>
    <p class="mt-12 text-xs text-ink-foreground/60">© <?= date('Y') ?> IdleWheels</p>
  </footer>
  <script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
