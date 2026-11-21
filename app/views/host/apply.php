<?php $p = $profile ?: []; ?>
<section class="grid gap-12 px-4 pt-10 sm:px-8 lg:grid-cols-[minmax(0,4fr)_minmax(0,7fr)] lg:gap-20 lg:px-12 lg:pt-14">
  <div>
    <p class="eyebrow">Host application</p>
    <h1 class="mt-2 font-display text-4xl font-extrabold tracking-tight">Apply to host</h1>
    <p class="mt-3 text-muted-foreground">Tell us who you are and how to pay you. We review every host before their first car goes live.</p>
    <a href="<?= url('/become-a-host') ?>" class="mt-6 inline-block text-sm font-semibold text-primary">How hosting works</a>
  </div>
  <div class="rounded-lg border border-border bg-background p-6 sm:p-10">
    <?php if ($profile && $profile['status'] === 'approved'): ?>
      <p class="font-display text-2xl font-semibold">You're an approved host</p>
      <p class="mt-2 text-muted-foreground">Add your car from the host dashboard. You can still update your details below.</p>
    <?php elseif ($profile && $profile['status'] === 'pending'): ?>
      <p class="mb-6 rounded-md bg-secondary p-4 text-sm">Your application is being reviewed. You can update your details below.</p>
    <?php elseif ($profile && $profile['status'] === 'rejected'): ?>
      <p class="mb-6 rounded-md bg-secondary p-4 text-sm">Your previous application wasn't approved<?= $profile['admin_note'] ? ': ' . e($profile['admin_note']) : '.' ?> Update your details and apply again.</p>
    <?php endif; ?>
    <form method="post" action="<?= url('/host/apply') ?>" class="mt-4 grid gap-5 sm:grid-cols-2">
      <?= csrf_field() ?>
      <label><span class="field-label">Your name *</span><input name="display_name" required maxlength="100" value="<?= e(old('display_name', $p['display_name'] ?? $user['name'])) ?>" class="field"></label>
      <label><span class="field-label">Phone number *</span><input name="phone" type="tel" required value="<?= e(old('phone', $p['phone'] ?? ($user['phone'] ?? ''))) ?>" class="field" placeholder="e.g. 024 123 4567"><?= error_for('phone') ?></label>
      <label><span class="field-label">City or area *</span><input name="city" required maxlength="100" value="<?= e(old('city', $p['city'] ?? '')) ?>" class="field" placeholder="e.g. East Legon, Accra"></label>
      <label><span class="field-label">Payout details *</span><input name="payout_method" required maxlength="150" value="<?= e(old('payout_method', $p['payout_method'] ?? '')) ?>" class="field" placeholder="Mobile money number or bank account"></label>
      <label class="sm:col-span-2"><span class="field-label">About you</span><textarea name="about" rows="4" maxlength="1000" class="field" placeholder="A few words renters will see, like how you hand over the keys"><?= e(old('about', $p['about'] ?? '')) ?></textarea></label>
      <label class="flex items-start gap-3 text-sm sm:col-span-2"><input type="checkbox" name="agree" value="1" class="mt-0.5 h-5 w-5 accent-[var(--color-primary)]">
        <span>I own or am authorised to rent out the car(s) I list, they are registered, roadworthy and insured, and I agree to the <a href="<?= url('/terms') ?>" target="_blank" class="text-primary">Terms</a> and <a href="<?= url('/insurance-safety') ?>" target="_blank" class="text-primary">Insurance &amp; safety</a> rules.</span></label>
      <div class="sm:col-span-2"><?= error_for('agree') ?></div>
      <div class="sm:col-span-2"><button class="btn-primary"><?= $profile ? 'Update application' : 'Apply to host' ?></button></div>
    </form>
  </div>
</section>
