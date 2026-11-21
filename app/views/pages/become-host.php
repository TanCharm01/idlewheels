<section class="grid items-center gap-10 px-4 pb-16 pt-12 sm:px-8 lg:grid-cols-[minmax(0,6fr)_minmax(0,5fr)] lg:gap-16 lg:px-12 lg:pt-20">
  <div>
    <p class="eyebrow">Host your car on IdleWheels</p>
    <h1 class="mt-4 font-display text-[2.5rem] font-extrabold leading-[1.03] tracking-tight sm:text-6xl">Your car is parked. It could be earning</h1>
    <p class="mt-6 max-w-xl text-lg leading-relaxed text-muted-foreground">Rent your car to people nearby by the day, on the days it would otherwise sit idle. You set the price and decide which bookings to accept</p>
    <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-4">
      <a href="<?= url($isHost ? '/host' : '/host/apply') ?>" class="btn-primary px-8 py-4 text-base"><?= $isHost ? 'Go to host dashboard' : 'Apply to host' ?></a>
      <a href="#how" class="text-base font-semibold text-primary underline decoration-2 underline-offset-4">How hosting works</a>
    </div>
  </div>
  <?php if ($photo): ?><div class="overflow-hidden rounded-lg bg-secondary"><img src="<?= e(photo_url($photo)) ?>" alt="" class="aspect-[6/5] w-full object-cover"></div><?php endif; ?>
</section>

<section class="bg-secondary px-4 py-20 sm:px-8 lg:px-12">
  <h2 class="max-w-2xl font-display text-3xl font-extrabold tracking-tight sm:text-4xl">What you get as a host</h2>
  <div class="mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
    <div><p class="font-display text-xl font-semibold">You set the price</p><p class="mt-2 text-muted-foreground">Choose a daily price and how many days your car can be rented for.</p></div>
    <div><p class="font-display text-xl font-semibold">You choose the bookings</p><p class="mt-2 text-muted-foreground">See each request with the renter's details and dates, then accept or decline it.</p></div>
    <div><p class="font-display text-xl font-semibold">Paid before you hand over</p><p class="mt-2 text-muted-foreground">Renters pay online when they book, by card or mobile money.</p></div>
    <div><p class="font-display text-xl font-semibold">Earnings in one place</p><p class="mt-2 text-muted-foreground">Your dashboard shows requests, upcoming rentals and what you've earned.</p></div>
  </div>
</section>

<section id="how" class="scroll-mt-20 px-4 py-20 sm:px-8 lg:px-12">
  <h2 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">How hosting works</h2>
  <ol class="mt-12 grid gap-10 md:grid-cols-4">
    <li><span class="font-display text-4xl font-extrabold text-primary">1</span><p class="mt-3 text-lg font-semibold">Create an account and apply</p><p class="mt-1 text-muted-foreground">Tell us your name, phone number, area, and how you'd like to be paid.</p></li>
    <li><span class="font-display text-4xl font-extrabold text-primary">2</span><p class="mt-3 text-lg font-semibold">Add your car</p><p class="mt-1 text-muted-foreground">Upload photos and add the specs, location and your daily price.</p></li>
    <li><span class="font-display text-4xl font-extrabold text-primary">3</span><p class="mt-3 text-lg font-semibold">We review and publish it</p><p class="mt-1 text-muted-foreground">The IdleWheels team checks your application and listing before it goes live.</p></li>
    <li><span class="font-display text-4xl font-extrabold text-primary">4</span><p class="mt-3 text-lg font-semibold">Accept bookings and earn</p><p class="mt-1 text-muted-foreground">Answer requests, hand over the keys, and track your earnings.</p></li>
  </ol>
</section>

<section class="mx-4 grid gap-10 rounded-lg border border-border p-8 sm:mx-8 sm:p-12 lg:mx-12 lg:grid-cols-2">
  <div>
    <h2 class="font-display text-2xl font-extrabold tracking-tight">What hosting costs</h2>
    <ul class="mt-5 space-y-3 text-muted-foreground">
      <li><span class="font-semibold text-foreground">Free to list.</span> There's no fee to apply or to list a car.</li>
      <li><span class="font-semibold text-foreground">Booking commission of <?= e(rtrim(rtrim(number_format($commission, 2), '0'), '.')) ?>%</span> is taken from each rental, including any extras the renter adds. You keep the rest.</li>
      <li><span class="font-semibold text-foreground">Optional featured listing:</span> <?= e(money_short($featuredPrice)) ?> puts a car at the top of search and on the home page for <?= (int) $featuredDays ?> days.</li>
      <li>Renters pay a separate service fee at checkout. It doesn't come out of your earnings.</li>
    </ul>
  </div>
  <div>
    <h2 class="font-display text-2xl font-extrabold tracking-tight">What you need to host</h2>
    <ul class="mt-5 space-y-3 text-muted-foreground">
      <li>A car you own, with valid registration and a roadworthy certificate</li>
      <li>Insurance that covers the car when other people drive it</li>
      <li>A phone number renters can reach you on</li>
      <li>A mobile money number or bank account for payouts</li>
    </ul>
    <p class="mt-5 text-sm">Read <a href="<?= url('/insurance-safety') ?>" class="font-semibold text-primary">Insurance &amp; safety</a>, the <a href="<?= url('/rental-policy') ?>" class="font-semibold text-primary">rental policy</a> and the <a href="<?= url('/terms') ?>" class="font-semibold text-primary">terms</a>.</p>
  </div>
</section>

<section class="mx-4 mt-16 grid items-center gap-8 rounded-lg bg-ink px-6 py-14 text-ink-foreground sm:mx-8 sm:px-12 lg:mx-12 lg:grid-cols-[1fr_auto]">
  <h2 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Ready to put your car to work?</h2>
  <a href="<?= url($isHost ? '/host' : '/host/apply') ?>" class="justify-self-start rounded-control bg-ink-foreground px-8 py-4 text-base font-semibold text-ink no-underline hover:opacity-90"><?= $isHost ? 'Go to host dashboard' : 'Apply to host' ?></a>
</section>
