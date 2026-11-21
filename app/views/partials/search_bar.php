<?php
$f = $filters ?? [];
$today = date('Y-m-d');
?>
<form action="<?= url('/cars') ?>" method="get" role="search" aria-label="Find a car" class="rounded-lg border border-border bg-background p-2 shadow-[0_24px_60px_-34px_hsl(195_35%_12%/0.45)]" data-date-pair>
  <div class="grid gap-1 md:grid-cols-[1.3fr_1fr_1fr_auto]">
    <label class="rounded-md px-4 py-2.5 hover:bg-secondary/60">
      <span class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Where</span>
      <select name="location" class="w-full bg-transparent text-base outline-none">
        <option value="">Anywhere</option>
        <?php foreach ($locations as $l): ?><option value="<?= e($l) ?>" <?= ($f['location'] ?? '') === $l ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label class="rounded-md px-4 py-2.5 hover:bg-secondary/60">
      <span class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Pick-up</span>
      <input type="date" name="from" min="<?= $today ?>" value="<?= e($f['from'] ?? '') ?>" class="w-full bg-transparent text-base outline-none" data-from>
    </label>
    <label class="rounded-md px-4 py-2.5 hover:bg-secondary/60">
      <span class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Return</span>
      <input type="date" name="to" min="<?= $today ?>" value="<?= e($f['to'] ?? '') ?>" class="w-full bg-transparent text-base outline-none" data-to>
    </label>
    <button class="btn-primary md:my-1 md:mr-1">Search cars</button>
  </div>
</form>
