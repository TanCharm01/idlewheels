<?php
use App\Core\View;
use App\Models\Booking;

$tabs = ['upcoming' => 'Upcoming', 'history' => 'History', 'saved' => 'Saved cars', 'profile' => 'Profile'];
?>
<section class="px-4 pt-10 sm:px-8 lg:px-12 lg:pt-14">
  <p class="eyebrow">Renter dashboard</p>
  <h1 class="mb-8 mt-2 font-display text-4xl font-extrabold tracking-tight">My account</h1>
  <div class="grid gap-8 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-12">
    <nav class="flex gap-2 overflow-x-auto lg:flex-col" aria-label="Account sections">
      <?php foreach ($tabs as $k => $label): ?>
        <a href="<?= url('/account', ['tab' => $k]) ?>" class="tab <?= $tab === $k ? 'tab-active' : '' ?>" <?= $tab === $k ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="<?= url('/host') ?>" class="tab text-primary">Host dashboard →</a>
    </nav>

    <div class="min-w-0">
      <?php if ($tab === 'upcoming'): ?>
        <?php if (!$upcoming): ?>
          <div class="rounded-lg border border-dashed border-border p-10 text-center">
            <p class="font-display text-xl font-semibold">No upcoming bookings</p>
            <a href="<?= url('/cars') ?>" class="btn-primary mt-4 inline-block">Browse cars</a>
          </div>
        <?php else: ?>
          <ul class="space-y-3"><?php foreach ($upcoming as $b): ?><?= View::partial('partials/booking_row', ['b' => $b, 'perspective' => 'renter']) ?><?php endforeach; ?></ul>
        <?php endif; ?>

      <?php elseif ($tab === 'history'): ?>
        <?php if (!$history): ?>
          <p class="rounded-lg border border-dashed border-border p-10 text-center text-muted-foreground">Your past rentals will appear here.</p>
        <?php else: ?>
          <ul class="space-y-3">
            <?php foreach ($history as $b):
              $actions = (Booking::isCompleted($b) && !$b['review_id'])
                ? '<a href="' . url('/bookings/' . $b['ref']) . '#review" class="btn-ghost mt-4 inline-block">Leave a review</a>'
                : ($b['review_id'] ? '<p class="mt-3 text-sm text-muted-foreground">You reviewed this rental.</p>' : '');
            ?>
              <?= View::partial('partials/booking_row', ['b' => $b, 'perspective' => 'renter', 'actions' => $actions]) ?>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

      <?php elseif ($tab === 'saved'): ?>
        <?php if (!$saved): ?>
          <p class="rounded-lg border border-dashed border-border p-10 text-center text-muted-foreground">Tap Save on a car's page to keep it here.</p>
        <?php else: ?>
          <ul class="divide-y divide-border rounded-lg border border-border bg-background">
            <?php foreach ($saved as $c): ?>
              <li class="flex items-center gap-4 p-4">
                <div class="h-14 w-20 shrink-0 overflow-hidden rounded-sm bg-secondary"><?php if ($c['photo']): ?><img src="<?= e(photo_url($c['photo'])) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?></div>
                <a href="<?= url('/cars/' . $c['slug']) ?>" class="min-w-0 flex-1 font-semibold text-foreground"><?= e($c['title']) ?><span class="block text-sm font-normal text-muted-foreground"><?= e(money_short($c['price_per_day'])) ?> per day · <?= e($c['location']) ?></span></a>
                <form method="post" action="<?= url('/cars/' . $c['slug'] . '/save') ?>"><?= csrf_field() ?><button class="text-sm text-muted-foreground hover:text-destructive">Remove</button></form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

      <?php else: ?>
        <div class="grid max-w-xl gap-8">
          <form method="post" action="<?= url('/account/profile') ?>" class="grid gap-4 rounded-lg border border-border bg-background p-6">
            <?= csrf_field() ?>
            <p class="font-semibold">Profile</p>
            <label><span class="field-label">Full name</span><input name="name" required value="<?= e(old('name', $user['name'])) ?>" class="field"></label>
            <?= error_for('name') ?>
            <label><span class="field-label">Email</span><input value="<?= e($user['email']) ?>" class="field" disabled></label>
            <label><span class="field-label">Phone</span><input name="phone" type="tel" value="<?= e(old('phone', $user['phone'] ?? '')) ?>" class="field"></label>
            <?= error_for('phone') ?>
            <p class="text-xs text-muted-foreground">Member since <?= e(fmt_date($user['created_at'], 'F Y')) ?></p>
            <button class="btn-primary justify-self-start">Save profile</button>
          </form>
          <form method="post" action="<?= url('/account/password') ?>" class="grid gap-4 rounded-lg border border-border bg-background p-6">
            <?= csrf_field() ?>
            <p class="font-semibold">Change password</p>
            <label><span class="field-label">Current password</span><input type="password" name="current_password" required class="field" autocomplete="current-password"></label>
            <?= error_for('current_password') ?>
            <label><span class="field-label">New password</span><input type="password" name="password" required minlength="8" maxlength="100" pattern="<?= e(App\Core\Validator::STRONG_PASSWORD_HTML) ?>" title="<?= e(App\Core\Validator::STRONG_PASSWORD_MESSAGE) ?>" class="field" autocomplete="new-password"></label>
            <p class="-mt-2 text-xs text-muted-foreground">At least 8 characters, with an uppercase letter, a lowercase letter, a number and a symbol (like ! @ # $).</p>
            <?= error_for('password') ?>
            <label><span class="field-label">Confirm new password</span><input type="password" name="password_confirm" required class="field" autocomplete="new-password"></label>
            <button class="btn-ghost justify-self-start">Change password</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
