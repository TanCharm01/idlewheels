<?php
use App\Models\Booking;

$status = Booking::displayStatus($b);
$headline = [
    'pending_payment' => 'Payment not completed',
    'awaiting_host' => 'Request sent to the host',
    'confirmed' => 'Your booking is confirmed',
    'completed' => 'Rental completed',
    'declined' => 'The host declined this request',
    'cancelled' => 'Booking cancelled',
][$status] ?? 'Booking';
if (!$isRenter) {
    $headline = 'Booking ' . $b['ref'];
}
$tone = in_array($status, ['confirmed', 'completed'], true) ? 'bg-primary text-primary-foreground' : (in_array($status, ['declined', 'cancelled'], true) ? 'bg-secondary text-muted-foreground' : 'bg-secondary text-accent');
?>
<section class="grid gap-10 px-4 pt-10 sm:px-8 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] lg:gap-14 lg:px-12 lg:pt-14">
  <div>
    <p class="eyebrow">Booking <?= e($b['ref']) ?></p>
    <h1 class="mt-2 font-display text-4xl font-extrabold tracking-tight"><?= e($headline) ?></h1>
    <p class="mt-3"><span class="rounded-control px-3 py-1 text-xs font-semibold <?= $tone ?>"><?= e(booking_status_label($status)) ?></span>
      <?php if ($b['payment_status'] === 'refund_due'): ?><span class="ml-2 rounded-control bg-secondary px-3 py-1 text-xs font-semibold text-accent">Refund due</span><?php endif; ?>
      <?php if ($b['payment_status'] === 'refunded'): ?><span class="ml-2 rounded-control bg-secondary px-3 py-1 text-xs font-semibold">Refunded</span><?php endif; ?>
    </p>

    <?php if ($isRenter): ?>
      <p class="mt-4 max-w-xl text-muted-foreground">
        <?php if ($status === 'awaiting_host'): ?>Your payment was received. The host will accept or decline your request; if they decline, you'll be refunded.
        <?php elseif ($status === 'confirmed'): ?>Your payment was received. Your host will contact you on <?= e($b['renter_phone']) ?> to arrange the hand-over.
        <?php elseif ($status === 'pending_payment'): ?>We haven't received payment for this booking. The dates are held for 30 minutes after you start booking.
        <?php elseif ($status === 'completed' && !$b['review_id']): ?>How was it? Leave a review below to help other renters.
        <?php endif; ?>
      </p>
    <?php endif; ?>

    <div class="mt-8 flex gap-5 rounded-lg border border-border bg-background p-5">
      <div class="h-24 w-36 shrink-0 overflow-hidden rounded-md bg-secondary"><?php if ($b['photo']): ?><img src="<?= e(photo_url($b['photo'])) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?></div>
      <div>
        <a href="<?= url('/cars/' . $b['car_slug']) ?>" class="font-display text-xl font-semibold text-foreground"><?= e($b['car_title']) ?></a>
        <p class="text-sm text-muted-foreground"><?= e($b['car_location']) ?></p>
        <p class="mt-2 text-sm"><span class="font-semibold"><?= e(fmt_date($b['start_date'])) ?></span> → <span class="font-semibold"><?= e(fmt_date($b['end_date'])) ?></span> · <?= (int) $b['days'] ?> days</p>
      </div>
    </div>

    <dl class="mt-6 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-[10rem_1fr]">
      <?php if ($isRenter || \App\Core\Auth::isAdmin()): ?>
        <dt class="text-muted-foreground">Host</dt><dd><?= e($b['host_name']) ?><?= in_array($status, ['confirmed', 'completed'], true) && $b['host_phone'] ? ' · ' . e($b['host_phone']) : '' ?></dd>
      <?php endif; ?>
      <?php if ($isHost || \App\Core\Auth::isAdmin()): ?>
        <dt class="text-muted-foreground">Renter</dt><dd><?= e($b['renter_name']) ?> · <?= e($b['renter_phone']) ?> · <?= e($b['renter_email']) ?></dd>
      <?php endif; ?>
      <?php if ($b['renter_note']): ?><dt class="text-muted-foreground">Renter's note</dt><dd class="whitespace-pre-line"><?= e($b['renter_note']) ?></dd><?php endif; ?>
      <?php if ($b['host_note']): ?><dt class="text-muted-foreground">Host's note</dt><dd class="whitespace-pre-line"><?= e($b['host_note']) ?></dd><?php endif; ?>
      <dt class="text-muted-foreground">Booked on</dt><dd><?= e(fmt_date($b['created_at'], 'j M Y, H:i')) ?></dd>
    </dl>

    <?php if ($isRenter): ?>
      <div class="mt-8 flex flex-wrap gap-3">
        <?php if ($status === 'pending_payment'): ?>
          <form method="post" action="<?= url('/bookings/' . $b['ref'] . '/pay') ?>"><?= csrf_field() ?><button class="btn-primary">Pay <?= e(money($b['total'])) ?></button></form>
        <?php endif; ?>
        <?php if (in_array($status, ['pending_payment', 'awaiting_host', 'confirmed'], true) && $b['start_date'] > date('Y-m-d')): ?>
          <form method="post" action="<?= url('/bookings/' . $b['ref'] . '/cancel') ?>" onsubmit="return confirm('Cancel this booking? Refunds follow the rental policy.')"><?= csrf_field() ?><button class="btn-ghost">Cancel booking</button></form>
        <?php endif; ?>
        <a href="<?= url('/account') ?>" class="btn-ghost">My bookings</a>
      </div>

      <?php if ($status === 'completed' && !$b['review_id']): ?>
        <form method="post" action="<?= url('/bookings/' . $b['ref'] . '/review') ?>" class="mt-10 grid max-w-xl gap-4 rounded-lg border border-border p-6">
          <?= csrf_field() ?>
          <p class="font-display text-xl font-semibold">Leave a review</p>
          <fieldset class="flex flex-row-reverse justify-end gap-1 text-3xl" data-stars>
            <legend class="sr-only">Rating</legend>
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" class="peer sr-only" <?= (int) old('rating') === $i ? 'checked' : '' ?> required>
              <label for="star<?= $i ?>" class="cursor-pointer text-border peer-checked:text-primary hover:text-primary" title="<?= $i ?> stars">★</label>
            <?php endfor; ?>
          </fieldset>
          <?= error_for('rating') ?>
          <label><span class="field-label">Your review</span><textarea name="comment" rows="4" maxlength="1500" required class="field" placeholder="How was the car and the hand-over?"><?= e(old('comment')) ?></textarea></label>
          <?= error_for('comment') ?>
          <button class="btn-primary justify-self-start">Post review</button>
        </form>
      <?php elseif ($b['review_id']): ?>
        <p class="mt-8 text-sm text-muted-foreground">You reviewed this rental. Thank you.</p>
      <?php endif; ?>
    <?php elseif ($isHost && $status === 'awaiting_host'): ?>
      <div class="mt-8"><a href="<?= url('/host', ['tab' => 'requests']) ?>" class="btn-primary">Answer this request</a></div>
    <?php endif; ?>
  </div>

  <aside>
    <div class="rounded-lg border border-border bg-background p-6 lg:sticky lg:top-24">
      <p class="field-label">Payment</p>
      <dl class="grid grid-cols-[1fr_auto] gap-x-4 gap-y-2 text-sm">
        <dt class="text-muted-foreground"><?= e(money_short($b['price_per_day'])) ?> × <?= (int) $b['days'] ?> days</dt><dd class="text-right"><?= e(money($b['rental_amount'])) ?></dd>
        <?php foreach ($addons as $a): ?>
          <dt class="text-muted-foreground"><?= e($a['name']) ?><?= $a['quantity'] > 1 ? ' × ' . (int) $a['quantity'] : '' ?></dt><dd class="text-right"><?= e(money($a['line_total'])) ?></dd>
        <?php endforeach; ?>
        <?php if ($isRenter || \App\Core\Auth::isAdmin()): ?>
          <dt class="text-muted-foreground">Service fee (<?= e((float) $b['service_fee_percent']) ?>%)</dt><dd class="text-right"><?= e(money($b['service_fee'])) ?></dd>
          <dt class="border-t border-border pt-3 font-semibold">Total paid</dt><dd class="border-t border-border pt-3 text-right font-display text-xl font-semibold"><?= e(money($b['total'])) ?></dd>
        <?php endif; ?>
        <?php if ($isHost || \App\Core\Auth::isAdmin()): ?>
          <dt class="text-muted-foreground">IdleWheels commission (<?= e((float) $b['commission_percent']) ?>%)</dt><dd class="text-right">−<?= e(money($b['commission'])) ?></dd>
          <dt class="border-t border-border pt-3 font-semibold">Host earns</dt><dd class="border-t border-border pt-3 text-right font-display text-xl font-semibold"><?= e(money($b['host_earnings'])) ?></dd>
        <?php endif; ?>
      </dl>
      <p class="mt-4 text-xs text-muted-foreground">Payment status: <?= e(str_replace('_', ' ', $b['payment_status'])) ?></p>
    </div>
  </aside>
</section>
