<?php
$legalTitle = isset($legalTitle) ? (string) $legalTitle : '';
$legalIntro = isset($legalIntro) ? (string) $legalIntro : '';
$legalEyebrow = (isset($legalEyebrow) && $legalEyebrow !== '') ? (string) $legalEyebrow : 'Policies';
$sections = isset($sections) && is_array($sections) ? $sections : [];
$body = isset($body) ? (string) $body : '';
$legalDraft = isset($legalDraft) ? (bool) $legalDraft : true;
$legalUpdated = isset($legalUpdated) ? (string) $legalUpdated : '2026-10-26';
?>
<header class="border-b border-border px-4 pb-12 pt-12 sm:px-8 lg:px-12 lg:pb-16 lg:pt-20">
  <p class="eyebrow"><?= e($legalEyebrow) ?></p>
  <h1 class="mt-3 max-w-4xl font-display text-4xl font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl"><?= e($legalTitle) ?></h1>
  <?php if ($legalIntro !== ''): ?>
  <p class="mt-5 max-w-2xl text-lg leading-relaxed text-muted-foreground"><?= e($legalIntro) ?></p>
  <?php endif; ?>
  <p class="mt-6 text-sm text-muted-foreground">Last updated: <time datetime="<?= e($legalUpdated) ?>"><?= e(date('j F Y', strtotime($legalUpdated))) ?></time></p>
  <?php if ($legalDraft): ?>
  <div role="note" class="mt-8 flex max-w-3xl gap-3 rounded-md border-2 border-destructive/60 bg-background px-5 py-4 text-destructive">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 shrink-0">
      <path d="M12 3 2 20h20L12 3Z" />
      <path d="M12 10v4M12 17.5v.01" />
    </svg>
    <p class="text-base font-semibold leading-relaxed">
      Draft template: have a qualified lawyer review this before launch. Text in [square brackets] must be replaced with real details.
    </p>
  </div>
  <?php endif; ?>
</header>

<div class="grid gap-10 px-4 pt-10 sm:px-8 lg:grid-cols-[minmax(0,16rem)_minmax(0,1fr)] lg:gap-16 lg:px-12 lg:pt-14">
  <nav aria-label="On this page" class="lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:self-start lg:overflow-y-auto">
    <p class="eyebrow">On this page</p>
    <ol class="mt-4 space-y-2 border-l border-border text-sm">
      <?php foreach (array_values($sections) as $i => $s): ?>
      <li>
        <a href="#<?= e($s['id']) ?>" class="-ml-px block border-l-2 border-transparent py-0.5 pl-4 text-muted-foreground no-underline transition-colors hover:border-primary hover:text-primary">
          <span class="tabular-nums"><?= (int) $i + 1 ?>.</span> <?= e($s['title']) ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ol>
  </nav>

  <article class="legal-body max-w-3xl">
<?= $body ?>
  </article>
</div>

<style>
  .legal-body section {
    scroll-margin-top: 6rem;
    padding-bottom: 2.5rem;
  }
  .legal-body section + section {
    border-top: 1px solid var(--color-border);
    padding-top: 2.5rem;
  }
  .legal-body h2 {
    font-family: var(--font-display);
    font-size: 1.625rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    line-height: 1.2;
    margin-bottom: 1rem;
  }
  .legal-body h3 {
    font-weight: 700;
    font-size: 1.0625rem;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
  }
  .legal-body p,
  .legal-body li {
    font-size: 1rem;
    line-height: 1.7;
  }
  .legal-body p + p,
  .legal-body p + ul,
  .legal-body p + ol,
  .legal-body ul + p,
  .legal-body ol + p,
  .legal-body table + p,
  .legal-body p + table {
    margin-top: 1rem;
  }
  .legal-body ul,
  .legal-body ol {
    padding-left: 1.25rem;
  }
  .legal-body ul {
    list-style: disc;
  }
  .legal-body ol {
    list-style: decimal;
  }
  .legal-body li + li {
    margin-top: 0.4rem;
  }
  .legal-body a {
    color: var(--color-primary);
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 3px;
  }
  .legal-body .placeholder {
    background: var(--color-secondary);
    border-radius: 4px;
    padding: 0 0.3em;
    font-weight: 600;
  }
  .legal-body table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9375rem;
  }
  .legal-body th,
  .legal-body td {
    text-align: left;
    vertical-align: top;
    padding: 0.75rem 0.75rem 0.75rem 0;
    border-bottom: 1px solid var(--color-border);
  }
  .legal-body th {
    font-weight: 700;
  }
</style>
