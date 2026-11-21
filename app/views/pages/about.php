<?php
$trust = [
    ['title' => 'Owners and listings are reviewed', 'body' => 'Every owner and every car is checked before a listing goes live, including registration, roadworthiness and insurance documents.'],
    ['title' => 'Renters review every trip', 'body' => 'After a booking, renters review the car and the owner, so the next person knows what to expect.'],
    ['title' => 'Terms are clear upfront', 'body' => 'Price, pick-up details, fuel, mileage and cancellation rules are set out before you book, not after.'],
];
?>
<section class="grid gap-10 px-4 pb-16 pt-12 sm:px-8 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] lg:items-end lg:gap-20 lg:px-12 lg:pb-24 lg:pt-20">
  <div>
    <p class="eyebrow">About IdleWheels</p>
    <h1 class="mt-3 font-display text-4xl font-extrabold leading-[1.03] tracking-tight sm:text-6xl lg:text-7xl">Most cars spend the day parked</h1>
    <p class="mt-6 max-w-xl text-lg leading-relaxed text-muted-foreground sm:text-xl">
      IdleWheels lets owners rent those idle hours to people nearby who need a car, by the day
    </p>
    <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-4">
      <a href="<?= url('/cars') ?>" class="rounded-control bg-primary px-8 py-4 text-base font-semibold text-primary-foreground no-underline transition-opacity hover:opacity-90">Browse cars</a>
      <a href="<?= url('/become-a-host') ?>" class="text-base font-semibold text-primary underline decoration-2 underline-offset-4">List your car</a>
    </div>
  </div>
  <div class="border-l-4 border-primary pl-6">
    <p class="text-lg leading-relaxed">
      A private car is usually used for a commute, a school run or an errand, then left in a driveway or car park until the next one. That is a useful vehicle doing
      nothing, while someone a few streets away is looking for a way to get somewhere.
    </p>
    <p class="mt-4 text-lg leading-relaxed">IdleWheels connects the two.</p>
  </div>
</section>

<section class="grid gap-px bg-border lg:grid-cols-2">
  <div class="bg-secondary px-4 py-16 sm:px-8 lg:px-12 lg:py-20">
    <p class="eyebrow text-primary">For renters</p>
    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">A car when you need one, for as long as you need it</h2>
    <ul class="mt-8 space-y-4 text-lg leading-relaxed">
      <li>Rent by the day for an errand or a trip, without a long-term commitment.</li>
      <li>Choose from cars listed by owners close to you, with photos, specs and rates on every listing.</li>
      <li>See the full price in GHS before you book, and pay online with card or mobile money through Paystack when you book.</li>
    </ul>
    <a href="<?= url('/how-it-works') ?>#renters" class="mt-8 inline-block font-semibold text-primary underline decoration-2 underline-offset-4">How renting works</a>
  </div>
  <div class="bg-background px-4 py-16 sm:px-8 lg:px-12 lg:py-20">
    <p class="eyebrow text-primary">For owners</p>
    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Earn from the hours your car sits still</h2>
    <ul class="mt-8 space-y-4 text-lg leading-relaxed">
      <li>List your car with your own price and the times it is free.</li>
      <li>Accept or decline each booking request from your dashboard.</li>
      <li>Track bookings and earnings in one place.</li>
    </ul>
    <a href="<?= url('/how-it-works') ?>#owners" class="mt-8 inline-block font-semibold text-primary underline decoration-2 underline-offset-4">How listing works</a>
  </div>
</section>

<section class="px-4 pt-20 sm:px-8 lg:px-12 lg:pt-24">
  <p class="eyebrow">Trust</p>
  <h2 class="mt-3 max-w-2xl font-display text-3xl font-extrabold tracking-tight sm:text-4xl">How we keep it trustworthy</h2>
  <div class="mt-12 grid gap-10 md:grid-cols-3 md:gap-12">
    <?php foreach ($trust as $i => $item): ?>
    <div class="border-t-2 border-primary pt-6">
      <span class="font-display text-3xl font-extrabold text-primary"><?= $i + 1 ?></span>
      <p class="mt-3 text-lg font-semibold"><?= e($item['title']) ?></p>
      <p class="mt-2 leading-relaxed text-muted-foreground"><?= e($item['body']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
  <p class="mt-12 text-muted-foreground">
    Read the <a href="<?= url('/rental-policy') ?>" class="font-semibold text-primary">Rental Policy</a>, <a href="<?= url('/insurance-safety') ?>" class="font-semibold text-primary">Insurance &amp; Safety</a> and <a href="<?= url('/terms') ?>" class="font-semibold text-primary">Terms of Service</a> for the full details.
  </p>
</section>
