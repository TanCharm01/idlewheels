<?php
use App\Models\Booking;

$status = Booking::displayStatus($b);
$tone = in_array($status, ['confirmed', 'completed'], true) ? 'bg-primary text-primary-foreground' : (in_array($status, ['declined', 'cancelled'], true) ? 'bg-secondary text-muted-foreground' : 'bg-secondary text-accent');
$amount = ($perspective ?? 'renter') === 'host' ? $b['host_earnings'] : $b['total'];
?>
<li class="rounded-lg border border-border bg-background p-5">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div class="flex gap-4">
      <div class="hidden h-16 w-24 shrink-0 overflow-hidden rounded-md bg-secondary sm:block"><?php if ($b['photo']): ?><img src="<?= e(photo_url($b['photo'])) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?></div>
      <div>
        <a href="<?= url('/bookings/' . $b['ref']) ?>" class="font-display text-lg font-semibold text-foreground"><?= e($b['car_title']) ?></a>
        <p class="text-sm text-muted-foreground"><?= e(fmt_date($b['start_date'])) ?> → <?= e(fmt_date($b['end_date'])) ?> · <?= (int) $b['days'] ?> days</p>
        <?php if (($perspective ?? 'renter') === 'host'): ?>
          <p class="mt-1 text-sm"><span class="font-semibold"><?= e($b['renter_name']) ?></span> · <a href="tel:<?= e($b['renter_phone']) ?>" class="text-primary"><?= e($b['renter_phone']) ?></a></p>
        <?php endif; ?>
        <p class="mt-1 text-xs text-muted-foreground">Ref <?= e($b['ref']) ?></p>
      </div>
    </div>
    <div class="text-right">
      <span class="rounded-control px-2.5 py-0.5 text-xs font-semibold <?= $tone ?>"><?= e(booking_status_label($status)) ?></span>
      <p class="mt-2 font-display text-lg font-semibold"><?= e(money($amount)) ?></p>
      <?php if (($perspective ?? 'renter') === 'host'): ?><p class="text-xs text-muted-foreground">you earn</p><?php endif; ?>
    </div>
  </div>
  <?= $actions ?? '' ?>
</li>
