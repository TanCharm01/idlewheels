<?php
use App\Core\Auth;
use App\Models\Car;
use App\Models\Review;
use App\Models\Setting;

$user = Auth::user();
$features = Car::features($car);
$specs = array_filter([
    'Type' => $car['category_name'],
    'Seats' => $car['seats'],
    'Transmission' => $car['transmission'],
    'Fuel' => $car['fuel'],
    'Year' => $car['year'],
    'Make' => $car['make'],
]);
$feePct = Setting::num('service_fee_percent', 7);
$stars = function ($n) { $n = (int) round((float) $n); return str_repeat('★', $n) . str_repeat('☆', 5 - $n); };
$heldDays = [];
foreach ($held as $h) {
    for ($d = strtotime($h['start_date']); $d < strtotime($h['end_date']); $d += 86400) {
        $heldDays[] = date('Y-m-d', $d);
    }
}
$pricing = [
    'pricePerDay' => (float) $car['price_per_day'],
    'minDays' => (int) $car['min_days'],
    'maxDays' => (int) $car['max_days'],
    'feePercent' => $feePct,
    'currency' => currency(),
    'held' => array_values(array_unique($heldDays)),
    'addons' => array_map(function ($a) {
        return ['id' => (int) $a['id'], 'price' => (float) $a['price'], 'perDay' => (bool) $a['per_day'], 'max' => (int) $a['max_quantity']];
    }, $addons),
];
?>
<div class="flex items-center justify-between gap-4 px-4 pt-6 sm:px-8 lg:px-12">
  <a href="<?= url('/cars') ?>" class="text-sm text-muted-foreground no-underline hover:text-primary">← All cars</a>
  <form method="post" action="<?= url('/cars/' . $car['slug'] . '/save') ?>"><?= csrf_field() ?>
    <button class="btn-ghost inline-flex items-center gap-2" aria-pressed="<?= $saved ? 'true' : 'false' ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" class="<?= $saved ? 'text-primary' : '' ?>"><path d="M12 21s-7.5-4.6-9.5-9.3C1.2 8.4 3.3 5 6.8 5c2 0 3.4 1.1 4.2 2.4h2C13.8 6.1 15.2 5 17.2 5c3.5 0 5.6 3.4 4.3 6.7C19.5 16.4 12 21 12 21z" fill="<?= $saved ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.8"/></svg>
      <?= $saved ? 'Saved' : 'Save' ?>
    </button>
  </form>
</div>

<div class="grid gap-10 px-4 pt-4 sm:px-8 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] lg:gap-14 lg:px-12">
  <div class="min-w-0 space-y-10">
    <div class="grid gap-2 <?= count($photos) > 1 ? 'sm:grid-cols-[2fr_1fr]' : '' ?>">
      <div class="h-56 overflow-hidden rounded-lg bg-secondary sm:h-80 lg:h-[26rem]">
        <?php if ($photos): ?><img src="<?= e(photo_url($photos[0]['path'])) ?>" alt="<?= e($car['title']) ?>" class="h-full w-full object-cover"><?php endif; ?>
      </div>
      <?php if (count($photos) > 1): ?>
        <div class="hidden gap-2 sm:grid">
          <?php foreach (array_slice($photos, 1, 2) as $i => $p): ?>
            <div class="overflow-hidden rounded-lg bg-secondary"><img src="<?= e(photo_url($p['path'])) ?>" alt="<?= e($car['title']) ?>, photo <?= $i + 2 ?>" loading="lazy" class="h-full w-full object-cover"></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
        <?php if ($car['is_featured']): ?><span class="rounded-control bg-primary px-3 py-1 text-primary-foreground">Featured</span><?php endif; ?>
        <span class="rounded-control bg-secondary px-3 py-1"><?= e($car['location']) ?></span>
        <?php if ($car['requires_approval']): ?><span class="rounded-control bg-secondary px-3 py-1 text-accent">Host approves requests</span><?php endif; ?>
      </div>
      <h1 class="mt-3 font-display text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl"><?= e($car['title']) ?></h1>
      <p class="mt-2 text-muted-foreground">
        <span class="font-display text-xl font-semibold text-primary"><?= e(money_short($car['price_per_day'])) ?></span> per day · rent for <?= (int) $car['min_days'] ?>–<?= (int) $car['max_days'] ?> days
        <?php if ((int) $car['review_count'] > 0): ?><a href="#reviews" class="ml-2 text-sm text-primary">★ <?= e($car['rating']) ?> (<?= (int) $car['review_count'] ?>)</a><?php endif; ?>
      </p>
    </div>

    <section>
      <h2 class="font-display text-2xl font-extrabold tracking-tight">About this car</h2>
      <?php if ($car['tagline']): ?><p class="mt-2 font-semibold"><?= e($car['tagline']) ?></p><?php endif; ?>
      <p class="mt-2 max-w-2xl whitespace-pre-line leading-relaxed text-muted-foreground"><?= e($car['description']) ?></p>
      <dl class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-3">
        <?php foreach ($specs as $k => $v): ?>
          <div class="bg-background p-4"><dt class="text-xs uppercase tracking-wider text-muted-foreground"><?= e($k) ?></dt><dd class="mt-1 font-semibold"><?= e($v) ?></dd></div>
        <?php endforeach; ?>
      </dl>
      <?php if ($features): ?>
        <ul class="mt-5 flex flex-wrap gap-2"><?php foreach ($features as $ft): ?><li class="rounded-control border border-border px-3 py-1 text-sm"><?= e($ft) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </section>

    <section>
      <h2 class="font-display text-2xl font-extrabold tracking-tight">Availability</h2>
      <p class="mb-4 mt-1 text-sm text-muted-foreground">Crossed-out days are already booked.</p>
      <div class="max-w-md rounded-lg border border-border bg-background p-5" data-calendar></div>
    </section>

    <section class="flex items-start gap-4 rounded-lg bg-secondary p-6">
      <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary font-display text-lg font-extrabold text-primary-foreground"><?= e(mb_substr($car['host_name'] ?: 'H', 0, 1)) ?></span>
      <div>
        <p class="text-xs uppercase tracking-wider text-muted-foreground">Your host</p>
        <p class="font-display text-lg font-semibold"><?= e($car['host_name'] ?: 'IdleWheels host') ?></p>
        <p class="mt-1 text-sm text-muted-foreground">Car kept in <?= e($car['location']) ?>.<?= $car['host_since'] ? ' Hosting on IdleWheels since ' . e(fmt_date($car['host_since'], 'F Y')) . '.' : '' ?> Your host contacts you before the rental to agree the hand-over.</p>
      </div>
    </section>

    <section id="reviews">
      <div class="flex flex-wrap items-baseline gap-3">
        <h2 class="font-display text-2xl font-extrabold tracking-tight">Reviews</h2>
        <?php if ($reviews): ?><p class="text-sm text-muted-foreground"><span class="text-primary"><?= $stars($car['rating']) ?></span> <?= e($car['rating']) ?> from <?= count($reviews) ?> <?= count($reviews) === 1 ? 'review' : 'reviews' ?></p><?php endif; ?>
      </div>
      <?php if (!$reviews): ?>
        <p class="mt-3 text-muted-foreground">No reviews yet. Renters can review this car from their account after the rental ends.</p>
      <?php else: ?>
        <ul class="mt-5 space-y-5">
          <?php foreach ($reviews as $r): ?>
            <li class="border-b border-border pb-5">
              <p class="text-primary" aria-label="<?= (int) $r['rating'] ?> out of 5"><?= $stars($r['rating']) ?></p>
              <p class="mt-2 whitespace-pre-line leading-relaxed"><?= e($r['comment']) ?></p>
              <p class="mt-2 text-sm text-muted-foreground"><?= e(Review::shortName($r['author'])) ?>, <?= e(fmt_date($r['created_at'], 'F Y')) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>

  <aside class="order-first lg:order-none">
    <form method="get" action="<?= url('/cars/' . $car['slug'] . '/book') ?>" class="rounded-lg border border-border bg-background p-5 shadow-[0_20px_50px_-30px_hsl(195_35%_12%/0.35)] sm:p-7 lg:sticky lg:top-24" data-booking='<?= e(json_encode($pricing)) ?>' data-date-pair>
      <p class="font-display text-xl font-semibold">Book this car</p>
      <div class="mt-4 grid grid-cols-2 gap-3">
        <label><span class="field-label">Pick-up</span><input type="date" name="from" required min="<?= date('Y-m-d') ?>" value="<?= e($from) ?>" class="field" data-from></label>
        <label><span class="field-label">Return</span><input type="date" name="to" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e($to) ?>" class="field" data-to></label>
      </div>
      <p class="mt-2 hidden text-sm text-destructive" data-date-error></p>

      <?php if ($addons): ?>
        <fieldset class="mt-6 border-t border-border pt-5">
          <legend class="field-label">Extras</legend>
          <ul class="grid gap-2">
            <?php $hasCounts = false; ?>
            <?php foreach ($addons as $a): ?>
              <?php $yesNo = (int) $a['max_quantity'] === 1; $hasCounts = $hasCounts || ($a['per_day'] && !$yesNo); ?>
              <li>
                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-md border border-border px-3 py-2 has-[:checked]:border-primary">
                  <span class="min-w-0">
                    <span class="block text-sm font-medium"><?= e($a['name']) ?></span>
                    <span class="text-xs text-muted-foreground"><?= e(money_short($a['price'])) ?><?= $a['per_day'] ? ' a day' : '' ?><?= $a['description'] ? ' · ' . e($a['description']) : '' ?></span>
                  </span>
                  <?php if ($yesNo): ?>
                    <input type="checkbox" name="addons[<?= (int) $a['id'] ?>]" value="1" class="h-5 w-5 shrink-0 cursor-pointer accent-primary" aria-label="Add <?= e($a['name']) ?>" data-addon="<?= (int) $a['id'] ?>">
                  <?php else: ?>
                    <input type="number" name="addons[<?= (int) $a['id'] ?>]" min="0" max="<?= (int) $a['max_quantity'] ?>" value="0" class="field w-16 text-center" aria-label="How many: <?= e($a['name']) ?>" data-addon="<?= (int) $a['id'] ?>">
                  <?php endif; ?>
                </label>
              </li>
            <?php endforeach; ?>
          </ul>
          <p class="mt-2 text-xs text-muted-foreground">Ticked per-day extras are charged for every day of the rental.<?= $hasCounts ? ' For the others, enter how many days you need them.' : '' ?></p>
        </fieldset>
      <?php endif; ?>

      <dl class="mt-6 hidden grid-cols-[1fr_auto] gap-x-4 gap-y-1.5 rounded-md bg-secondary p-4 text-sm" data-estimate>
        <dt class="text-muted-foreground" data-rental-label>Car rental</dt><dd class="text-right" data-rental></dd>
        <dt class="text-muted-foreground">Extras</dt><dd class="text-right" data-extras></dd>
        <dt class="text-muted-foreground">Service fee (<?= e(rtrim(rtrim(number_format($feePct, 2), '0'), '.')) ?>%)</dt><dd class="text-right" data-fee></dd>
        <dt class="border-t border-border pt-2 font-semibold">Total</dt><dd class="border-t border-border pt-2 text-right font-display text-lg font-semibold" data-total></dd>
      </dl>

      <button class="btn-primary mt-5 w-full"><?= $car['requires_approval'] ? 'Review request' : 'Review booking' ?></button>
      <p class="mt-3 text-center text-xs text-muted-foreground">You won't be charged yet. Next you'll see the full summary<?= $user ? '' : ' and log in' ?>.</p>
    </form>
  </aside>
</div>
