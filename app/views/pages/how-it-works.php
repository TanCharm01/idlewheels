<?php
$renterSteps = [
    ['title' => 'Find a car', 'body' => 'Search the cars on offer and use the filters to narrow the list down. Each listing shows its photos, specs and rate.'],
    ['title' => 'Pick your dates and see the total', 'body' => 'Choose a pick-up time and how long you need the car, by the day. The full price in GHS is shown before you book.'],
    ['title' => 'Book and pay at checkout', 'body' => 'Confirm the booking at checkout and pay online with card or mobile money through Paystack when you book. Some owners approve each request first.'],
    ['title' => 'Pick up from the owner', 'body' => 'Meet the owner at the agreed place and time, show your driving licence, and walk around the car together before you drive off.'],
    ['title' => 'Return it and leave a review', 'body' => 'Bring the car back on time with the same fuel level, then review the car and the owner to help the next renter.'],
];

$ownerSteps = [
    ['title' => 'Create an account and apply to host', 'body' => 'Sign up and tell us about yourself and your car. We check that you are the owner or have the owner\'s permission.'],
    ['title' => 'Add your car', 'body' => 'Upload clear photos, set your daily price, and add the specs, location and the times the car is free.'],
    ['title' => 'We review and approve the listing', 'body' => 'We check the details and documents, including registration, roadworthiness and insurance, before the listing goes live.'],
    ['title' => 'Accept or decline requests', 'body' => 'Booking requests arrive in your dashboard. Accept the ones that suit you and decline the rest.'],
    ['title' => 'Track your earnings', 'body' => 'See upcoming and past bookings and what each one earned, all in one place.'],
];
?>
<section class="px-4 pb-16 pt-12 sm:px-8 lg:px-12 lg:pb-20 lg:pt-20">
  <p class="eyebrow">How it works</p>
  <h1 class="mt-3 max-w-4xl font-display text-4xl font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl">How IdleWheels works</h1>
  <p class="mt-5 max-w-2xl text-lg leading-relaxed text-muted-foreground sm:text-xl">
    Renters book idle cars from local owners by the day. Owners earn from their car when they are not using it
  </p>
  <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-4">
    <a href="<?= url('/cars') ?>" class="rounded-control bg-primary px-8 py-4 text-base font-semibold text-primary-foreground no-underline transition-opacity hover:opacity-90">Browse cars</a>
    <a href="#owners" class="text-base font-semibold text-primary underline decoration-2 underline-offset-4">I own a car</a>
  </div>
</section>

<section id="renters" class="scroll-mt-20 bg-secondary px-4 py-20 sm:px-8 lg:px-12">
  <div class="grid gap-12 lg:grid-cols-[minmax(0,4fr)_minmax(0,7fr)] lg:gap-20">
    <div class="lg:sticky lg:top-28 lg:self-start">
      <p class="eyebrow text-primary">For renters</p>
      <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Rent a car near you</h2>
      <p class="mt-4 max-w-md leading-relaxed text-muted-foreground">From search to return in five steps, with the price clear before you commit.</p>
      <a href="<?= url('/cars') ?>" class="mt-8 inline-block text-base font-semibold text-primary underline decoration-2 underline-offset-4">Find a car</a>
    </div>
    <ol class="space-y-9">
      <?php foreach ($renterSteps as $i => $step): ?>
      <li class="grid grid-cols-[3rem_1fr]">
        <span class="font-display text-3xl font-extrabold text-primary"><?= $i + 1 ?></span>
        <div>
          <p class="text-lg font-semibold"><?= e($step['title']) ?></p>
          <p class="mt-1 leading-relaxed text-muted-foreground"><?= e($step['body']) ?></p>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section id="owners" class="scroll-mt-20 px-4 py-20 sm:px-8 lg:px-12">
  <div class="grid gap-12 lg:grid-cols-[minmax(0,4fr)_minmax(0,7fr)] lg:gap-20">
    <div class="lg:sticky lg:top-28 lg:self-start">
      <p class="eyebrow text-primary">For owners</p>
      <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Earn from your parked car</h2>
      <p class="mt-4 max-w-md leading-relaxed text-muted-foreground">You set the price and the times. You decide who drives it.</p>
      <a href="<?= url('/become-a-host') ?>" class="mt-8 inline-block text-base font-semibold text-primary underline decoration-2 underline-offset-4">Apply to host</a>
    </div>
    <ol class="space-y-9">
      <?php foreach ($ownerSteps as $i => $step): ?>
      <li class="grid grid-cols-[3rem_1fr]">
        <span class="font-display text-3xl font-extrabold text-primary"><?= $i + 1 ?></span>
        <div>
          <p class="text-lg font-semibold"><?= e($step['title']) ?></p>
          <p class="mt-1 leading-relaxed text-muted-foreground"><?= e($step['body']) ?></p>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="border-t border-border px-4 pt-20 sm:px-8 lg:px-12">
  <div class="grid gap-12 lg:grid-cols-2 lg:gap-20">
    <div>
      <p class="eyebrow">Before you book</p>
      <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">What you need to rent</h2>
      <ul class="mt-8 space-y-5">
        <li class="flex gap-3">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 shrink-0 text-accent"><path d="M5 12.5 10 17 19 7" /></svg>
          <span class="text-lg">A valid driving licence for the type of car you book</span>
        </li>
        <li class="flex gap-3">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 shrink-0 text-accent"><path d="M5 12.5 10 17 19 7" /></svg>
          <span class="text-lg">A phone number and email address the owner can reach you on</span>
        </li>
        <li class="flex gap-3">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 shrink-0 text-accent"><path d="M5 12.5 10 17 19 7" /></svg>
          <span class="text-lg">Be at least 21 years old</span>
        </li>
      </ul>
    </div>
    <div>
      <p class="eyebrow">Read the details</p>
      <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Questions and policies</h2>
      <ul class="mt-8 divide-y divide-border border-y border-border">
        <li>
          <a href="<?= url('/faq') ?>" class="flex items-center justify-between gap-4 py-5 text-foreground no-underline transition-colors hover:text-primary">
            <span><span class="block text-lg font-semibold">FAQ</span><span class="text-sm text-muted-foreground">Common questions from renters and owners</span></span>
            <span aria-hidden="true" class="text-primary">&rarr;</span>
          </a>
        </li>
        <li>
          <a href="<?= url('/rental-policy') ?>" class="flex items-center justify-between gap-4 py-5 text-foreground no-underline transition-colors hover:text-primary">
            <span><span class="block text-lg font-semibold">Rental Policy, Cancellation &amp; Refunds</span><span class="text-sm text-muted-foreground">Pick-up, return, fuel, cancellations and refunds</span></span>
            <span aria-hidden="true" class="text-primary">&rarr;</span>
          </a>
        </li>
        <li>
          <a href="<?= url('/insurance-safety') ?>" class="flex items-center justify-between gap-4 py-5 text-foreground no-underline transition-colors hover:text-primary">
            <span><span class="block text-lg font-semibold">Insurance &amp; Safety</span><span class="text-sm text-muted-foreground">Cover, requirements and what to do if something goes wrong</span></span>
            <span aria-hidden="true" class="text-primary">&rarr;</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</section>
