<?php
$query = $query ?? '';
$facts = array_filter([$car['seats'] ? $car['seats'] . ' seats' : '', $car['transmission'], $car['fuel']]);
?>
<a href="<?= url('/cars/' . $car['slug']) . e($query) ?>" class="group flex h-full flex-col overflow-hidden rounded-lg border <?= $car['is_featured'] ? 'border-primary/60' : 'border-border' ?> bg-background no-underline transition-shadow hover:shadow-[0_12px_32px_-16px_hsl(195_35%_12%/0.35)]">
  <div class="relative aspect-[10/7] overflow-hidden bg-secondary">
    <?php if ($car['photo']): ?>
      <img src="<?= e(photo_url($car['photo'])) ?>" alt="<?= e($car['title']) ?>" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]">
    <?php else: ?>
      <div class="flex h-full items-center justify-center text-sm text-muted-foreground">No photo yet</div>
    <?php endif; ?>
    <span class="absolute left-3 top-3 flex gap-1.5">
      <?php if ($car['is_featured']): ?><span class="rounded-control bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground">Featured</span><?php endif; ?>
      <?php if (!empty($car['category_name'])): ?><span class="rounded-control bg-background/95 px-3 py-1 text-xs font-semibold text-foreground"><?= e($car['category_name']) ?></span><?php endif; ?>
    </span>
  </div>
  <div class="flex flex-1 flex-col p-5">
    <div class="flex items-start justify-between gap-3">
      <h3 class="font-display text-lg font-semibold leading-snug tracking-tight text-foreground"><?= e($car['title']) ?></h3>
      <p class="shrink-0 text-right">
        <span class="block font-display text-lg font-semibold text-primary"><?= e(money_short($car['price_per_day'])) ?></span>
        <span class="block text-xs text-muted-foreground">per day</span>
      </p>
    </div>
    <p class="mt-1 text-sm text-muted-foreground"><?= e($car['location']) ?></p>
    <ul class="mt-3 flex flex-wrap gap-1.5">
      <?php foreach ($facts as $f): ?><li class="rounded-control bg-secondary px-2.5 py-0.5 text-xs text-secondary-foreground"><?= e($f) ?></li><?php endforeach; ?>
    </ul>
    <div class="mt-auto flex flex-wrap items-center gap-x-3 gap-y-1 pt-4 text-xs text-muted-foreground">
      <?php if (!empty($car['host_name'])): ?><span>Hosted by <?= e($car['host_name']) ?></span><?php endif; ?>
      <?php if ((int) $car['review_count'] > 0): ?><span class="text-primary">★ <?= e($car['rating']) ?> (<?= (int) $car['review_count'] ?>)</span><?php endif; ?>
      <?php if ($car['requires_approval']): ?><span class="font-medium text-accent">Host approves requests</span><?php endif; ?>
    </div>
  </div>
</a>
