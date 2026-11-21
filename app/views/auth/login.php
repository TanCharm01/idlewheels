<section class="grid gap-12 px-4 pt-12 sm:px-8 lg:grid-cols-2 lg:gap-20 lg:px-12 lg:pt-20">
  <div class="w-full max-w-md lg:order-2">
    <h1 class="font-display text-3xl font-extrabold tracking-tight">Log in</h1>
    <p class="mb-8 mt-2 text-muted-foreground">One account for renting cars and hosting your own</p>
    <form method="post" action="<?= url('/login', ['return' => $return]) ?>" class="grid gap-4">
      <?= csrf_field() ?>
      <label><span class="field-label">Email</span><input type="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>" class="field"></label>
      <?= error_for('email') ?>
      <label><span class="field-label">Password</span><input type="password" name="password" required autocomplete="current-password" class="field"></label>
      <button class="btn-primary">Log in</button>
    </form>
    <p class="mt-6 text-sm text-muted-foreground">New to IdleWheels? <a href="<?= url('/register', ['return' => $return]) ?>" class="font-semibold text-primary">Create an account</a></p>
  </div>
  <div class="rounded-lg bg-secondary p-8 lg:p-12">
    <p class="eyebrow">With an IdleWheels account you can</p>
    <ul class="mt-6 space-y-5">
      <li><p class="font-semibold">Book cars</p><p class="text-sm text-muted-foreground">Rent by the day and follow each booking.</p></li>
      <li><p class="font-semibold">Save cars for later</p><p class="text-sm text-muted-foreground">Keep a list of the cars you like.</p></li>
      <li><p class="font-semibold">Review your rentals</p><p class="text-sm text-muted-foreground">Help other renters after each trip.</p></li>
      <li><p class="font-semibold">Host your own car</p><p class="text-sm text-muted-foreground">Apply to host, list your car and manage bookings.</p></li>
    </ul>
  </div>
</section>
