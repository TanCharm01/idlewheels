<?php
use App\Core\View;

$f = $filters;
$dateQuery = ($f['from'] && $f['to']) ? '?' . http_build_query(['from' => $f['from'], 'to' => $f['to']]) : '';
$active = count(array_filter([$f['location'], $f['from'], $f['type'], $f['seats'], $f['transmission'], $f['max_price'], $f['q']]));
?>
<section class="px-4 pb-8 pt-10 sm:px-8 lg:px-12 lg:pt-14">
  <p class="eyebrow">Cars from local owners</p>
  <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight sm:text-5xl">Browse cars to rent</h1>
  <p class="mt-3 max-w-2xl text-lg text-muted-foreground">Filter by where and when you need a car, then book it online</p>
</section>

<section class="grid gap-8 px-4 sm:px-8 lg:grid-cols-[17rem_minmax(0,1fr)] lg:gap-12 lg:px-12">
  <aside>
    <button type="button" class="btn-ghost mb-4 w-full lg:hidden" data-toggle="#filters" aria-expanded="false">Filters<?= $active ? " ($active)" : '' ?></button>
    <form id="filters" method="get" action="<?= url('/cars') ?>" class="hidden rounded-lg border border-border bg-background p-5 lg:sticky lg:top-24 lg:block" data-date-pair>
      <div class="grid gap-5">
        <label><span class="field-label">Search</span><input name="q" value="<?= e($f['q']) ?>" placeholder="Make or model" class="field"></label>
        <label><span class="field-label">Location</span>
          <select name="location" class="field"><option value="">Anywhere</option>
            <?php foreach ($locations as $l): ?><option <?= $f['location'] === $l ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
          </select></label>
        <div class="grid grid-cols-2 gap-3">
          <label><span class="field-label">Pick-up</span><input type="date" name="from" min="<?= date('Y-m-d') ?>" value="<?= e($f['from']) ?>" class="field" data-from></label>
          <label><span class="field-label">Return</span><input type="date" name="to" min="<?= date('Y-m-d') ?>" value="<?= e($f['to']) ?>" class="field" data-to></label>
        </div>
        <label><span class="field-label">Car type</span>
          <select name="type" class="field"><option value="">Any</option>
            <?php foreach ($categories as $c): ?><option value="<?= e($c['slug']) ?>" <?= $f['type'] === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
          </select></label>
        <label><span class="field-label">Seats</span>
          <select name="seats" class="field"><option value="">Any</option>
            <?php foreach ([2, 4, 5, 7] as $n): ?><option value="<?= $n ?>" <?= $f['seats'] === (string) $n ? 'selected' : '' ?>><?= $n ?>+ seats</option><?php endforeach; ?>
          </select></label>
        <label><span class="field-label">Transmission</span>
          <select name="transmission" class="field"><option value="">Any</option>
            <?php foreach (['Automatic', 'Manual'] as $t): ?><option <?= $f['transmission'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
          </select></label>
        <label><span class="field-label">Max price per day (<?= e(currency()) ?>)</span><input type="number" name="max_price" min="0" step="5" value="<?= e($f['max_price']) ?>" class="field" placeholder="No limit"></label>
        <label><span class="field-label">Sort by</span>
          <select name="sort" class="field">
            <option value="">Newest</option>
            <option value="price_asc" <?= $f['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
            <option value="price_desc" <?= $f['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
          </select></label>
        <button class="btn-primary">Show cars</button>
        <?php if ($active): ?><a href="<?= url('/cars') ?>" class="text-sm font-semibold text-primary">Clear all filters</a><?php endif; ?>
      </div>
    </form>
  </aside>
  <div>
    <p class="mb-5 text-sm text-muted-foreground"><?= count($cars) ?> <?= count($cars) === 1 ? 'car' : 'cars' ?><?= $f['from'] && $f['to'] ? ' available ' . e(fmt_date($f['from'], 'j M')) . ' – ' . e(fmt_date($f['to'], 'j M')) : '' ?></p>
    <?php if ($cars): ?>
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($cars as $car): ?><?= View::partial('partials/car_card', ['car' => $car, 'query' => $dateQuery]) ?><?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="rounded-lg border border-dashed border-border px-6 py-16 text-center">
        <p class="font-display text-xl font-semibold">No cars match these filters</p>
        <p class="mt-2 text-sm text-muted-foreground">Try other dates, a wider area, or fewer filters.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
