<section class="grid gap-12 px-4 pt-12 sm:px-8 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-20 lg:px-12 lg:pt-20">
  <div>
    <p class="eyebrow">Contact IdleWheels</p>
    <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight sm:text-5xl">Get in touch</h1>
    <p class="mt-4 max-w-md text-lg text-muted-foreground">Send us a message about a booking, hosting your car, or anything else</p>
    <div class="mt-10 space-y-5 text-sm">
      <p><span class="block font-semibold">Quick answers</span><a href="<?= url('/faq') ?>" class="text-primary">Read the FAQ</a></p>
      <p><span class="block font-semibold">About a booking?</span><span class="text-muted-foreground">Include your booking reference from <a href="<?= url('/account') ?>" class="text-primary">My account</a> so we can find it.</span></p>
      <p><span class="block font-semibold">Accident or breakdown?</span><span class="text-muted-foreground">Follow the steps in <a href="<?= url('/insurance-safety') ?>" class="text-primary">Insurance &amp; safety</a> first.</span></p>
    </div>
  </div>
  <form method="post" action="<?= url('/contact') ?>" class="grid gap-5 rounded-lg border border-border bg-background p-6 sm:grid-cols-2 sm:p-10">
    <?= csrf_field() ?>
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
    <label><span class="field-label">Your name *</span><input name="name" required maxlength="100" value="<?= e(old('name')) ?>" class="field"><?= error_for('name') ?></label>
    <label><span class="field-label">Email *</span><input type="email" name="email" required value="<?= e(old('email')) ?>" class="field"><?= error_for('email') ?></label>
    <label><span class="field-label">Phone</span><input type="tel" name="phone" value="<?= e(old('phone')) ?>" class="field"><?= error_for('phone') ?></label>
    <label><span class="field-label">Booking reference</span><input name="booking_ref" maxlength="20" value="<?= e(old('booking_ref')) ?>" class="field" placeholder="If your message is about a booking"></label>
    <fieldset class="sm:col-span-2"><legend class="field-label">I am a *</legend>
      <div class="flex flex-wrap gap-2">
        <?php foreach (['Renter', 'Car owner', 'Something else'] as $t): ?>
          <label class="chip-check"><input type="radio" name="topic" value="<?= $t ?>" required class="peer sr-only" <?= old('topic') === $t ? 'checked' : '' ?>><span class="peer-checked:border-primary peer-checked:bg-primary peer-checked:text-primary-foreground"><?= $t ?></span></label>
        <?php endforeach; ?>
      </div><?= error_for('topic') ?></fieldset>
    <label class="sm:col-span-2"><span class="field-label">Message *</span><textarea name="message" rows="6" required maxlength="3000" class="field"><?= e(old('message')) ?></textarea><?= error_for('message') ?></label>
    <div class="sm:col-span-2"><button class="btn-primary">Send message</button></div>
  </form>
</section>
