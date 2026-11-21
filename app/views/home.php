<?php use App\Core\View; ?>
<section class="relative overflow-hidden px-4 pb-16 pt-12 sm:px-8 lg:px-12 lg:pb-24 lg:pt-20">
  <div class="pointer-events-none absolute -right-40 -top-40 h-[34rem] w-[34rem] rounded-full bg-secondary" aria-hidden="true"></div>
  <div class="relative max-w-4xl">
    <p class="eyebrow">Peer-to-peer car rental in Ghana</p>
    <h1 class="mt-4 font-display text-[2.6rem] font-extrabold leading-[1.02] tracking-tight sm:text-6xl lg:text-7xl">Rent the car parked down the street</h1>
    <p class="mt-6 max-w-2xl text-lg leading-relaxed text-muted-foreground sm:text-xl">Book idle cars from local owners by the day, and earn from your own car when you're not driving it</p>
  </div>
  <div class="relative mt-10 max-w-5xl">
    <?= View::partial('partials/search_bar', ['locations' => $locations]) ?>
    <p class="mt-4 text-sm text-muted-foreground">Have a car that sits idle? <a href="<?= url('/become-a-host') ?>" class="font-semibold text-primary">Become a host</a></p>
  </div>
</section>

<?php if ($categories): ?>
<section class="px-4 sm:px-8 lg:px-12">
  <h2 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Browse by type</h2>
  <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <?php foreach ($categories as $c): ?>
      <a href="<?= url('/cars', ['type' => $c['slug']]) ?>" class="group relative block overflow-hidden rounded-lg bg-ink no-underline">
        <?php if ($c['image']): ?>
          <img src="<?= e(photo_url($c['image'])) ?>" alt="" loading="lazy" class="aspect-[4/3] w-full object-cover opacity-80 transition duration-500 group-hover:scale-105">
        <?php else: ?>
          <div class="aspect-[4/3] w-full bg-gradient-to-br from-primary to-ink"></div>
        <?php endif; ?>
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/90 to-transparent p-4 pt-12 text-ink-foreground">
          <p class="font-display text-xl font-semibold"><?= e($c['name']) ?></p>
          <p class="text-xs text-ink-foreground/80"><?= e($c['blurb']) ?><?= (int) $c['car_count'] > 0 ? ' · ' . (int) $c['car_count'] . ' available' : '' ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="mt-20 px-4 sm:px-8 lg:px-12">
  <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div><p class="eyebrow">Available to book</p><h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Featured cars</h2></div>
    <a href="<?= url('/cars') ?>" class="text-sm font-semibold text-primary">See all cars →</a>
  </div>
  <?php if ($cars): ?>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($cars as $car): ?><?= View::partial('partials/car_card', ['car' => $car]) ?><?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="rounded-lg border border-dashed border-border px-6 py-14 text-center text-muted-foreground">No cars are listed yet. Have one sitting idle? <a href="<?= url('/become-a-host') ?>" class="font-semibold text-primary">Become a host</a>.</div>
  <?php endif; ?>
</section>

<section id="how-it-works" class="mt-24 bg-secondary px-4 py-20 sm:px-8 lg:px-12">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <h2 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">How IdleWheels works</h2>
    <a href="<?= url('/how-it-works') ?>" class="text-sm font-semibold text-primary">The full guide →</a>
  </div>
  <div class="mt-12 grid gap-14 lg:grid-cols-2 lg:gap-20">
    <div>
      <p class="eyebrow text-primary">Renting a car</p>
      <ol class="mt-6 space-y-7">
        <li class="grid grid-cols-[2.5rem_1fr]"><span class="font-display text-3xl font-extrabold text-primary">1</span><div><p class="text-lg font-semibold">Search and pick a car</p><p class="mt-1 text-muted-foreground">Filter by where and when, type, seats, transmission and price.</p></div></li>
        <li class="grid grid-cols-[2.5rem_1fr]"><span class="font-display text-3xl font-extrabold text-primary">2</span><div><p class="text-lg font-semibold">Add extras and pay securely</p><p class="mt-1 text-muted-foreground">See the full price upfront and pay with card or mobile money. Some hosts approve each request first.</p></div></li>
        <li class="grid grid-cols-[2.5rem_1fr]"><span class="font-display text-3xl font-extrabold text-primary">3</span><div><p class="text-lg font-semibold">Drive, return, review</p><p class="mt-1 text-muted-foreground">Collect the car from the host and leave a review from your account afterwards.</p></div></li>
      </ol>
    </div>
    <div>
      <p class="eyebrow text-primary">Hosting your car</p>
      <ol class="mt-6 space-y-7">
        <li class="grid grid-cols-[2.5rem_1fr]"><span class="font-display text-3xl font-extrabold text-primary">1</span><div><p class="text-lg font-semibold">Apply to host</p><p class="mt-1 text-muted-foreground">Create an account and tell us about yourself.</p></div></li>
        <li class="grid grid-cols-[2.5rem_1fr]"><span class="font-display text-3xl font-extrabold text-primary">2</span><div><p class="text-lg font-semibold">Add your car</p><p class="mt-1 text-muted-foreground">Photos, specs and your daily price. We review it before it goes live.</p></div></li>
        <li class="grid grid-cols-[2.5rem_1fr]"><span class="font-display text-3xl font-extrabold text-primary">3</span><div><p class="text-lg font-semibold">Accept bookings, track earnings</p><p class="mt-1 text-muted-foreground">Answer requests and follow your earnings in the host dashboard.</p></div></li>
      </ol>
    </div>
  </div>
</section>

<section class="mx-4 mt-16 grid items-center gap-8 rounded-lg bg-ink px-6 py-14 text-ink-foreground sm:mx-8 sm:px-12 lg:mx-12 lg:grid-cols-[1fr_auto]">
  <div>
    <h2 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Your car sits still most of the day</h2>
    <p class="mt-3 max-w-xl text-ink-foreground/80">Host it on IdleWheels and rent it out by the day, on the days you choose</p>
  </div>
  <a href="<?= url('/become-a-host') ?>" class="justify-self-start rounded-control bg-ink-foreground px-8 py-4 text-base font-semibold text-ink no-underline hover:opacity-90">Become a host</a>
</section>
