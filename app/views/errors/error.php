<section class="px-4 py-24 text-center sm:px-8">
  <p class="eyebrow">Error <?= (int) ($status ?? 500) ?></p>
  <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight"><?= ($status ?? 500) === 403 ? 'You can\'t open this page' : 'Something went wrong' ?></h1>
  <p class="mt-3 text-muted-foreground"><?= e($message ?: 'Please try again in a moment.') ?></p>
  <a href="<?= url('/') ?>" class="btn-primary mt-8 inline-block">Go to the home page</a>
</section>
