<?php
use App\Core\View;
use App\Models\Booking;

$tabs = ['overview' => 'Overview', 'listings' => 'My listings', 'requests' => 'Booking requests', 'earnings' => 'Earnings'];
$today = date('Y-m-d');
$requests = array_values(array_filter($bookings, function ($b) { return $b['status'] === 'awaiting_host'; }));
$upcoming = array_values(array_filter($bookings, function ($b) use ($today) { return $b['status'] === 'confirmed' && $b['end_date'] > $today; }));
$completed = array_values(array_filter($bookings, function ($b) { return Booking::isCompleted($b); }));
$sum = function (array $rows, string $k) { return array_sum(array_map(function ($r) use ($k) { return (float) $r[$k]; }, $rows)); };
$earned = $sum($completed, 'host_earnings');
$expected = $sum($upcoming, 'host_earnings') + $sum($requests, 'host_earnings');
$commissionPaid = $sum($completed, 'commission');
$statusLabel = ['approved' => 'Live', 'pending' => 'In review', 'rejected' => 'Not approved', 'hidden' => 'Hidden'];
?>
<section class="px-4 pt-10 sm:px-8 lg:px-12 lg:pt-14">
  <p class="eyebrow">Host dashboard</p>
  <h1 class="mb-8 mt-2 font-display text-4xl font-extrabold tracking-tight">Your cars and bookings</h1>

  <?php if (!$profile || $profile['status'] !== 'approved'): ?>
    <div class="max-w-2xl rounded-lg border border-border p-8">
      <p class="font-display text-2xl font-semibold"><?= !$profile ? 'Become a host' : ($profile['status'] === 'pending' ? 'Your application is being reviewed' : 'Your application wasn\'t approved') ?></p>
      <p class="mt-2 text-muted-foreground"><?= !$profile ? 'Apply to host to list your car, manage bookings and track earnings here.' : ($profile['status'] === 'pending' ? 'We usually reply within two working days. You can add cars once you\'re approved.' : e($profile['admin_note'] ?: 'Update your details and apply again.')) ?></p>
      <a href="<?= url('/host/apply') ?>" class="btn-primary mt-6 inline-block"><?= !$profile ? 'Apply to host' : 'View application' ?></a>
    </div>
  <?php else: ?>
  <div class="grid gap-8 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-12">
    <nav class="flex gap-2 overflow-x-auto lg:flex-col" aria-label="Host dashboard sections">
      <?php foreach ($tabs as $k => $label): ?>
        <a href="<?= url('/host', ['tab' => $k]) ?>" class="tab <?= $tab === $k ? 'tab-active' : '' ?>"><?= e($label) ?><?php if ($k === 'requests' && $requests): ?> <span class="ml-1 rounded-full bg-accent px-2 py-0.5 text-xs text-primary-foreground"><?= count($requests) ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <a href="<?= url('/host/cars/new') ?>" class="btn-ghost shrink-0 text-center lg:mt-4">+ Add a car</a>
    </nav>

    <div class="min-w-0">
      <?php if ($tab === 'overview'): ?>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <?php foreach ([
              ['You earned', money($earned), count($completed) . ' completed, after commission'],
              ['Coming up', money($expected), count($upcoming) + count($requests) . ' upcoming or requested'],
              ['Requests', (string) count($requests), 'Waiting for your answer'],
              ['Live cars', (string) count(array_filter($cars, function ($c) { return $c['status'] === 'approved'; })), count($cars) . ' listed in total'],
          ] as [$l, $v, $h]): ?>
            <div class="stat"><p class="stat-label"><?= e($l) ?></p><p class="stat-value"><?= e($v) ?></p><p class="stat-hint"><?= e($h) ?></p></div>
          <?php endforeach; ?>
        </div>
        <h2 class="mb-4 mt-10 font-display text-2xl font-extrabold tracking-tight">Upcoming rentals</h2>
        <?php if (!$upcoming): ?><p class="text-muted-foreground">No confirmed rentals coming up.</p>
        <?php else: ?><ul class="space-y-3"><?php foreach (array_reverse($upcoming) as $b): ?><?= View::partial('partials/booking_row', ['b' => $b, 'perspective' => 'host']) ?><?php endforeach; ?></ul><?php endif; ?>

      <?php elseif ($tab === 'listings'): ?>
        <?php if (!$cars): ?>
          <div class="rounded-lg border border-dashed border-border p-10 text-center">
            <p class="font-display text-xl font-semibold">You haven't added a car yet</p>
            <a href="<?= url('/host/cars/new') ?>" class="btn-primary mt-4 inline-block">Add your first car</a>
          </div>
        <?php else: ?>
          <p class="mb-5 text-sm text-muted-foreground">Promote a live car to the top of search and the home page for <?= (int) $featuredDays ?> days for <?= e(money_short($featuredPrice)) ?>.</p>
          <ul class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($cars as $c): ?>
              <li class="overflow-hidden rounded-lg border <?= $c['is_featured'] ? 'border-primary/60' : 'border-border' ?> bg-background">
                <div class="aspect-[10/7] bg-secondary"><?php if ($c['photo']): ?><img src="<?= e(photo_url($c['photo'])) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?></div>
                <div class="p-4">
                  <div class="flex items-start justify-between gap-2">
                    <p class="font-display text-lg font-semibold leading-snug"><?= e($c['title']) ?></p>
                    <span class="shrink-0 rounded-control px-2.5 py-0.5 text-xs font-semibold <?= $c['status'] === 'approved' ? 'bg-primary text-primary-foreground' : 'bg-secondary' ?>"><?= e($statusLabel[$c['status']] ?? $c['status']) ?></span>
                  </div>
                  <p class="mt-1 text-sm text-muted-foreground"><?= e($c['location']) ?> · <?= e(money_short($c['price_per_day'])) ?> / day</p>
                  <?php if ($c['status'] === 'rejected' && $c['admin_note']): ?><p class="mt-2 text-sm text-destructive"><?= e($c['admin_note']) ?></p><?php endif; ?>
                  <?php if ($c['is_featured']): ?><p class="mt-2 text-sm font-semibold text-primary">Featured until <?= e(fmt_date($c['featured_until'], 'j M Y, H:i')) ?></p><?php endif; ?>
                  <div class="mt-4 flex flex-wrap items-center gap-4 text-sm font-semibold">
                    <a href="<?= url('/host/cars/' . $c['id'] . '/edit') ?>" class="text-primary">Edit</a>
                    <?php if ($c['status'] === 'approved'): ?>
                      <a href="<?= url('/cars/' . $c['slug']) ?>" class="text-primary">View live page</a>
                      <form method="post" action="<?= url('/host/cars/' . $c['id'] . '/feature') ?>" onsubmit="return confirm('Pay <?= e(money_short($featuredPrice)) ?> to feature this car for <?= (int) $featuredDays ?> days?')">
                        <?= csrf_field() ?><button class="rounded-control border border-primary px-3 py-1 text-primary" <?= $paystackReady ? '' : 'disabled title="Online payments are not set up yet"' ?>><?= $c['is_featured'] ? 'Extend +' . (int) $featuredDays . ' days' : 'Feature this car' ?></button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

      <?php elseif ($tab === 'requests'): ?>
        <h2 class="mb-4 font-display text-2xl font-extrabold tracking-tight">Waiting for your answer</h2>
        <?php if (!$requests): ?><p class="text-muted-foreground">No requests right now. Cars that need your approval show requests here.</p>
        <?php else: ?>
          <ul class="space-y-3">
            <?php foreach ($requests as $b):
              $actions = '<form method="post" class="mt-4 flex flex-wrap items-center gap-3 border-t border-border pt-4">' . csrf_field()
                . '<input name="note" maxlength="500" placeholder="Message to the renter (optional)" class="field max-w-sm">'
                . '<button formaction="' . url('/host/bookings/' . $b['ref'] . '/accept') . '" class="btn-primary">Accept</button>'
                . '<button formaction="' . url('/host/bookings/' . $b['ref'] . '/decline') . '" class="btn-ghost" onclick="return confirm(\'Decline this request? The renter will be refunded.\')">Decline</button></form>';
            ?>
              <?= View::partial('partials/booking_row', ['b' => $b, 'perspective' => 'host', 'actions' => $actions]) ?>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <h2 class="mb-4 mt-10 font-display text-2xl font-extrabold tracking-tight">Upcoming rentals</h2>
        <?php if (!$upcoming): ?><p class="text-muted-foreground">No confirmed rentals coming up.</p>
        <?php else: ?><ul class="space-y-3"><?php foreach (array_reverse($upcoming) as $b): ?><?= View::partial('partials/booking_row', ['b' => $b, 'perspective' => 'host']) ?><?php endforeach; ?></ul><?php endif; ?>

      <?php else: ?>
        <div class="mb-8 grid gap-4 sm:grid-cols-3">
          <div class="stat"><p class="stat-label">You earned</p><p class="stat-value"><?= e(money($earned)) ?></p><p class="stat-hint">Completed rentals, after commission</p></div>
          <div class="stat"><p class="stat-label">Expected</p><p class="stat-value"><?= e(money($expected)) ?></p><p class="stat-hint">Upcoming and requested</p></div>
          <div class="stat"><p class="stat-label">Commission paid</p><p class="stat-value"><?= e(money($commissionPaid)) ?></p><p class="stat-hint"><?= e((float) $commissionPercent) ?>% on completed rentals</p></div>
        </div>
        <p class="mb-4 text-sm text-muted-foreground">Payouts are sent by the IdleWheels team to <?= e($profile['payout_method']) ?>. Renters' service fees are not part of your earnings.</p>
        <?php $rows = array_merge($upcoming, $completed); if (!$rows): ?>
          <p class="rounded-lg border border-dashed border-border p-10 text-center text-muted-foreground">Your earnings will appear here after your first confirmed rental.</p>
        <?php else: ?>
          <div class="overflow-x-auto rounded-lg border border-border">
            <table class="table">
              <thead><tr><th>Car</th><th>Dates</th><th>Renter</th><th>Status</th><th class="text-right">Rental + extras</th><th class="text-right">Commission</th><th class="text-right">You earn</th></tr></thead>
              <tbody>
                <?php foreach ($rows as $b): ?>
                  <tr>
                    <td class="font-semibold"><a href="<?= url('/bookings/' . $b['ref']) ?>" class="text-foreground"><?= e($b['car_title']) ?></a></td>
                    <td><?= e(fmt_date($b['start_date'], 'j M')) ?> – <?= e(fmt_date($b['end_date'], 'j M Y')) ?></td>
                    <td><?= e($b['renter_name']) ?></td>
                    <td><?= Booking::isCompleted($b) ? 'Completed' : 'Upcoming' ?></td>
                    <td class="text-right"><?= e(money((float) $b['rental_amount'] + (float) $b['addons_amount'])) ?></td>
                    <td class="text-right text-muted-foreground">−<?= e(money($b['commission'])) ?></td>
                    <td class="text-right font-semibold"><?= e(money($b['host_earnings'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</section>
