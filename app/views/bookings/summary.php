<?php
use App\Core\Auth;
use App\Core\Paystack;

$user = Auth::user();
$q = $quote;
$hidden = function () use ($from, $to, $qty) {
    $h = '<input type="hidden" name="from" value="' . e($from) . '"><input type="hidden" name="to" value="' . e($to) . '">';
    foreach ($qty as $id => $n) {
        $h .= '<input type="hidden" name="addons[' . (int) $id . ']" value="' . (int) $n . '">';
    }
    return $h;
};
$editUrl = url('/cars/' . $car['slug'], ['from' => $from, 'to' => $to]);
?>
<section class="grid gap-10 px-4 pt-10 sm:px-8 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] lg:gap-14 lg:px-12 lg:pt-14">
  <div>
    <p class="eyebrow">Booking summary</p>
    <h1 class="mt-2 font-display text-4xl font-extrabold tracking-tight"><?= $car['requires_approval'] ? 'Review your request' : 'Review your booking' ?></h1>
    <div class="mt-8 flex gap-5 rounded-lg border border-border bg-background p-5">
      <div class="h-24 w-36 shrink-0 overflow-hidden rounded-md bg-secondary"><?php if ($photos): ?><img src="<?= e(photo_url($photos[0]['path'])) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?></div>
      <div>
        <p class="font-display text-xl font-semibold"><?= e($car['title']) ?></p>
        <p class="text-sm text-muted-foreground"><?= e($car['location']) ?> · hosted by <?= e($car['host_name']) ?></p>
        <p class="mt-2 text-sm"><span class="font-semibold"><?= e(fmt_date($from)) ?></span> → <span class="font-semibold"><?= e(fmt_date($to)) ?></span> · <?= (int) $q['days'] ?> <?= $q['days'] === 1 ? 'day' : 'days' ?></p>
        <a href="<?= e($editUrl) ?>" class="mt-1 inline-block text-sm font-semibold text-primary">Change dates or extras</a>
      </div>
    </div>

    <?php if (!$user): ?>
      <div class="mt-8 rounded-lg border border-border p-6">
        <p class="font-semibold">Log in or create an account to book</p>
        <p class="mt-1 text-sm text-muted-foreground">Your bookings, receipts and messages from the host are kept in your account.</p>
        <?php $ret = '/cars/' . $car['slug'] . '/book?' . http_build_query(['from' => $from, 'to' => $to, 'addons' => $qty]); ?>
        <div class="mt-4 flex flex-wrap gap-3">
          <a href="<?= url('/login', ['return' => $ret]) ?>" class="btn-primary">Log in</a>
          <a href="<?= url('/register', ['return' => $ret]) ?>" class="btn-ghost">Create an account</a>
        </div>
      </div>
    <?php else: ?>
      <form method="post" action="<?= url('/cars/' . $car['slug'] . '/book') ?>" class="mt-8 grid gap-5 rounded-lg border border-border bg-background p-6">
        <?= csrf_field() ?><?= $hidden() ?>
        <p class="font-semibold">Your details</p>
        <p class="-mt-3 text-sm text-muted-foreground">Booking as <?= e($user['name']) ?> (<?= e($user['email']) ?>)</p>
        <label><span class="field-label">Phone number the host can reach you on *</span><input name="phone" type="tel" required value="<?= e($user['phone'] ?? '') ?>" class="field" placeholder="e.g. 024 123 4567"></label>
        <label><span class="field-label">Note for the host (optional)</span><textarea name="note" rows="3" maxlength="500" class="field" placeholder="Pick-up time you'd prefer, where you're coming from…"></textarea></label>
        <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="agree" value="1" required class="mt-0.5 h-5 w-5 accent-[var(--color-primary)]">
          <span>I have a valid driving licence and agree to the <a href="<?= url('/rental-policy') ?>" target="_blank" class="text-primary">rental &amp; cancellation policy</a> and <a href="<?= url('/terms') ?>" target="_blank" class="text-primary">terms</a>.</span></label>
        <?php if (!$paystackReady): ?>
          <p class="flash flash-error">Online payments aren't set up yet: the site owner needs to add Paystack keys. You can't complete a booking until then.</p>
        <?php endif; ?>
        <button class="btn-primary" <?= $paystackReady ? '' : 'disabled' ?>>Pay <?= e(money($q['total'])) ?> securely</button>
        <p class="text-center text-xs text-muted-foreground">You'll be taken to Paystack to pay by card or mobile money<?= Paystack::isTestMode() ? ' (test mode: no real money is charged)' : '' ?>.<?= $car['requires_approval'] ? ' If the host declines your request, you\'re refunded.' : '' ?></p>
      </form>
    <?php endif; ?>
  </div>

  <aside>
    <div class="rounded-lg border border-border bg-background p-6 lg:sticky lg:top-24">
      <p class="field-label">Price details</p>
      <dl class="grid grid-cols-[1fr_auto] gap-x-4 gap-y-2 text-sm">
        <dt class="text-muted-foreground"><?= e(money_short($q['price_per_day'])) ?> × <?= (int) $q['days'] ?> <?= $q['days'] === 1 ? 'day' : 'days' ?></dt><dd class="text-right"><?= e(money($q['rental'])) ?></dd>
        <?php foreach ($q['addons'] as $a): ?>
          <dt class="text-muted-foreground"><?= e($a['name']) ?><?= $a['quantity'] > 1 ? ' × ' . (int) $a['quantity'] : '' ?></dt><dd class="text-right"><?= e(money($a['line_total'])) ?></dd>
        <?php endforeach; ?>
        <dt class="text-muted-foreground"><?= e(\App\Models\Setting::get('service_fee_name', 'Service fee')) ?> (<?= e(rtrim(rtrim(number_format($q['service_fee_percent'], 2), '0'), '.')) ?>%)</dt><dd class="text-right"><?= e(money($q['service_fee'])) ?></dd>
        <dt class="border-t border-border pt-3 text-base font-semibold">Total</dt><dd class="border-t border-border pt-3 text-right font-display text-2xl font-semibold"><?= e(money($q['total'])) ?></dd>
      </dl>
      <p class="mt-4 text-xs text-muted-foreground">The service fee covers secure payments and running IdleWheels. Prices are in <?= e(currency()) ?>.</p>
    </div>
  </aside>
</section>
