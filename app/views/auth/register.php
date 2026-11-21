<section class="grid gap-12 px-4 pt-12 sm:px-8 lg:grid-cols-2 lg:gap-20 lg:px-12 lg:pt-20">
  <div class="w-full max-w-md lg:order-2">
    <h1 class="font-display text-3xl font-extrabold tracking-tight">Create an account</h1>
    <p class="mb-8 mt-2 text-muted-foreground">Rent cars from local owners, or host your own</p>
    <form method="post" action="<?= url('/register', ['return' => $return]) ?>" class="grid gap-4">
      <?= csrf_field() ?>
      <label><span class="field-label">Full name</span><input name="name" required autocomplete="name" value="<?= e(old('name')) ?>" class="field"></label>
      <?= error_for('name') ?>
      <label><span class="field-label">Email</span><input type="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>" class="field"></label>
      <?= error_for('email') ?>
      <label><span class="field-label">Phone (optional)</span><input type="tel" name="phone" autocomplete="tel" value="<?= e(old('phone')) ?>" class="field" placeholder="e.g. 024 123 4567"></label>
      <?= error_for('phone') ?>
      <label><span class="field-label">Password</span><input type="password" name="password" required minlength="8" maxlength="100" pattern="<?= e(App\Core\Validator::STRONG_PASSWORD_HTML) ?>" title="<?= e(App\Core\Validator::STRONG_PASSWORD_MESSAGE) ?>" autocomplete="new-password" class="field"></label>
      <p class="-mt-2 text-xs text-muted-foreground">At least 8 characters, with an uppercase letter, a lowercase letter, a number and a symbol (like ! @ # $).</p>
      <?= error_for('password') ?>
      <label><span class="field-label">Confirm password</span><input type="password" name="password_confirm" required autocomplete="new-password" class="field"></label>
      <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="terms" value="1" class="mt-0.5 h-5 w-5 accent-[var(--color-primary)]">
        <span>I am 21 or older and agree to the <a href="<?= url('/terms') ?>" target="_blank" class="text-primary">Terms</a> and <a href="<?= url('/privacy') ?>" target="_blank" class="text-primary">Privacy Policy</a>.</span></label>
      <?= error_for('terms') ?>
      <button class="btn-primary">Create account</button>
    </form>
    <p class="mt-6 text-sm text-muted-foreground">Already have an account? <a href="<?= url('/login', ['return' => $return]) ?>" class="font-semibold text-primary">Log in</a></p>
  </div>
  <div class="rounded-lg bg-secondary p-8 lg:p-12">
    <p class="eyebrow">Why IdleWheels</p>
    <ul class="mt-6 space-y-5">
      <li><p class="font-semibold">Cars nearby</p><p class="text-sm text-muted-foreground">Rent from owners in your area instead of a rental desk.</p></li>
      <li><p class="font-semibold">Clear prices</p><p class="text-sm text-muted-foreground">See the full total, extras and fees included, before you pay.</p></li>
      <li><p class="font-semibold">Secure payment</p><p class="text-sm text-muted-foreground">Pay by card or mobile money through Paystack.</p></li>
    </ul>
  </div>
</section>
