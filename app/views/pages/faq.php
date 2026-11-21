<section class="px-4 pt-12 sm:px-8 lg:px-12 lg:pt-16">
  <p class="eyebrow">Help for renters and hosts</p>
  <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight sm:text-5xl">Frequently asked questions</h1>
  <p class="mt-3 max-w-2xl text-lg text-muted-foreground">Browse by topic, or search for a word</p>
</section>
<section class="mt-8 grid gap-10 px-4 sm:px-8 lg:grid-cols-[16rem_minmax(0,1fr)] lg:px-12">
  <div class="lg:sticky lg:top-24 lg:self-start">
    <input type="search" placeholder="Search questions" class="field" data-faq-search aria-label="Search questions">
    <nav class="mt-4 grid gap-1 text-sm" aria-label="FAQ topics">
      <?php foreach (array_keys($faqs) as $cat): ?><a href="#<?= e(slugify($cat)) ?>" class="rounded-md px-3 py-2 text-foreground no-underline hover:bg-secondary"><?= e($cat) ?></a><?php endforeach; ?>
    </nav>
  </div>
  <div class="max-w-3xl space-y-10">
    <?php foreach ($faqs as $cat => $items): ?>
      <div id="<?= e(slugify($cat)) ?>" class="scroll-mt-24" data-faq-group>
        <h2 class="mb-3 font-display text-2xl font-extrabold tracking-tight"><?= e($cat) ?></h2>
        <?php foreach ($items as $f): ?>
          <details class="border-b border-border py-4" data-faq-item>
            <summary class="cursor-pointer list-none font-semibold"><?= e($f['question']) ?></summary>
            <p class="mt-2 leading-relaxed text-muted-foreground"><?= e($f['answer']) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <p class="rounded-lg bg-secondary p-6 text-sm">Didn't find your answer? <a href="<?= url('/contact') ?>" class="font-semibold text-primary">Contact us</a></p>
  </div>
</section>
