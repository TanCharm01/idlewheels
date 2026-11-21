<?php
use App\Models\Car;

$c = $car ?: [];
$val = function ($k, $d = '') use ($c) { return old($k, $c[$k] ?? $d); };
$ideas = ['Air conditioning', 'Bluetooth audio', 'Apple CarPlay / Android Auto', 'Reversing camera', 'USB charging', 'Roof rack', 'Tow bar', 'Four-wheel drive'];
$current = $car ? Car::features($car) : (array) old('features', []);
$allFeatures = array_values(array_unique(array_merge($ideas, $current)));
$action = $car ? url('/host/cars/' . $car['id']) : url('/host/cars');
?>
<section class="px-4 pt-10 sm:px-8 lg:px-12 lg:pt-14">
  <a href="<?= url('/host', ['tab' => 'listings']) ?>" class="text-sm text-muted-foreground no-underline hover:text-primary">← My listings</a>
  <h1 class="mb-2 mt-3 font-display text-4xl font-extrabold tracking-tight"><?= $car ? 'Edit your car' : 'Add your car' ?></h1>
  <?php if ($car && $car['status'] === 'approved'): ?><p class="mb-6 text-sm text-muted-foreground">This car is live. Changes show on its page straight away.</p>
  <?php elseif (!$car): ?><p class="mb-6 text-sm text-muted-foreground">The IdleWheels team reviews every car before it goes live.</p><?php endif; ?>

  <?php if ($car && $photos): ?>
    <div class="mb-8 max-w-4xl">
      <p class="field-label">Current photos</p>
      <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
        <?php foreach ($photos as $p): ?>
          <div class="relative aspect-[4/3] overflow-hidden rounded-md bg-secondary">
            <img src="<?= e(photo_url($p['path'])) ?>" alt="" class="h-full w-full object-cover">
            <form method="post" action="<?= url('/host/cars/' . $car['id'] . '/photos/' . $p['id'] . '/delete') ?>" class="absolute right-1.5 top-1.5" onsubmit="return confirm('Remove this photo?')">
              <?= csrf_field() ?><button class="rounded-control bg-background/95 px-2.5 py-1 text-xs font-semibold">Remove</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="grid max-w-4xl gap-8">
    <?= csrf_field() ?>
    <fieldset>
      <legend class="font-display text-xl font-semibold">Photos</legend>
      <p class="mb-3 text-sm text-muted-foreground">Up to 5 clear photos (JPG, PNG or WebP, max <?= e(App\Core\Upload::mb(App\Core\Upload::maxBytes())) ?> each, <?= e(App\Core\Upload::mb(App\Core\Upload::maxPostBytes())) ?> in total). The first is the cover.</p>
      <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple <?= $car ? '' : 'required' ?> class="block w-full text-sm file:mr-4 file:rounded-control file:border-0 file:bg-secondary file:px-4 file:py-2 file:font-semibold">
      <?= error_for('photos') ?>
    </fieldset>

    <fieldset class="grid gap-5 sm:grid-cols-3">
      <legend class="mb-3 font-display text-xl font-semibold sm:col-span-3">The car</legend>
      <label><span class="field-label">Make *</span><input name="make" required value="<?= e($val('make')) ?>" class="field" placeholder="Toyota"><?= error_for('make') ?></label>
      <label><span class="field-label">Model *</span><input name="model" required value="<?= e($val('model')) ?>" class="field" placeholder="Corolla"><?= error_for('model') ?></label>
      <label><span class="field-label">Year *</span><input type="number" name="year" required min="1990" max="<?= (int) date('Y') + 1 ?>" value="<?= e($val('year')) ?>" class="field"><?= error_for('year') ?></label>
      <label><span class="field-label">Type *</span><select name="category_id" required class="field"><option value="">Choose…</option>
        <?php foreach ($categories as $cat): ?><option value="<?= (int) $cat['id'] ?>" <?= (string) $val('category_id') === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></label>
      <label><span class="field-label">Seats *</span><input type="number" name="seats" required min="2" max="15" value="<?= e($val('seats', '5')) ?>" class="field"></label>
      <label><span class="field-label">Transmission *</span><select name="transmission" required class="field"><option value="">Choose…</option>
        <?php foreach (['Automatic', 'Manual'] as $t): ?><option <?= $val('transmission') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
      <label><span class="field-label">Fuel *</span><select name="fuel" required class="field"><option value="">Choose…</option>
        <?php foreach (['Petrol', 'Diesel', 'Hybrid', 'Electric'] as $t): ?><option <?= $val('fuel') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
      <label class="sm:col-span-2"><span class="field-label">Where is it kept? *</span><input name="location" required value="<?= e($val('location')) ?>" class="field" placeholder="Neighbourhood, City"><?= error_for('location') ?></label>
    </fieldset>

    <fieldset class="grid gap-5 sm:grid-cols-3">
      <legend class="mb-3 font-display text-xl font-semibold sm:col-span-3">Price and availability</legend>
      <label><span class="field-label">Price per day (<?= e(currency()) ?>) *</span><input type="number" name="price_per_day" required min="5" step="0.5" value="<?= e($val('price_per_day')) ?>" class="field"><?= error_for('price_per_day') ?></label>
      <label><span class="field-label">Minimum days *</span><input type="number" name="min_days" required min="1" max="30" value="<?= e($val('min_days', '1')) ?>" class="field"></label>
      <label><span class="field-label">Maximum days *</span><input type="number" name="max_days" required min="1" max="60" value="<?= e($val('max_days', '14')) ?>" class="field"></label>
      <label class="flex items-start gap-3 text-sm sm:col-span-3"><input type="checkbox" name="requires_approval" value="1" <?= $val('requires_approval', '1') ? 'checked' : '' ?> class="mt-0.5 h-5 w-5 accent-[var(--color-primary)]">
        <span><span class="font-semibold">I want to approve each booking.</span> Renters pay when they request; you accept or decline from your dashboard. Untick to let renters book instantly.</span></label>
    </fieldset>

    <fieldset class="grid gap-5">
      <legend class="mb-3 font-display text-xl font-semibold">Description</legend>
      <label><span class="field-label">Short tagline</span><input name="tagline" maxlength="160" value="<?= e($val('tagline')) ?>" class="field" placeholder="e.g. Frugal city runabout"></label>
      <label><span class="field-label">Description *</span><textarea name="description" rows="6" required maxlength="3000" class="field" placeholder="Condition, what it's good for, how hand-over works"><?= e($val('description')) ?></textarea><?= error_for('description') ?></label>
      <div>
        <span class="field-label">Features</span>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($allFeatures as $ft): ?>
            <label class="chip-check"><input type="checkbox" name="features[]" value="<?= e($ft) ?>" <?= in_array($ft, $current, true) ? 'checked' : '' ?> class="sr-only peer"><span class="peer-checked:border-primary peer-checked:bg-primary peer-checked:text-primary-foreground"><?= e($ft) ?></span></label>
          <?php endforeach; ?>
        </div>
        <input name="features_other" class="field mt-3 max-w-md" placeholder="Other features, separated by commas">
      </div>
    </fieldset>

    <div class="flex flex-wrap items-center gap-4">
      <button class="btn-primary"><?= $car ? 'Save changes' : 'Submit car for review' ?></button>
      <a href="<?= url('/host', ['tab' => 'listings']) ?>" class="text-sm font-semibold text-muted-foreground">Cancel</a>
    </div>
  </form>
</section>
