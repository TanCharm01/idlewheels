<?php
use App\Models\Booking;
use App\Models\Review;

$tabs = [
    'overview' => 'Overview', 'hosts' => 'Hosts', 'cars' => 'Car listings', 'bookings' => 'Bookings', 'users' => 'Users',
    'revenue' => 'Revenue', 'addons' => 'Extras', 'categories' => 'Categories', 'reviews' => 'Reviews', 'messages' => 'Messages', 'settings' => 'Fees & settings',
];
$chip = function (string $s) {
    $tone = in_array($s, ['approved', 'confirmed', 'completed', 'active'], true) ? 'bg-primary text-primary-foreground' : (in_array($s, ['pending', 'awaiting_host', 'pending_payment'], true) ? 'bg-secondary text-accent' : 'bg-secondary text-muted-foreground');
    return '<span class="rounded-control px-2.5 py-0.5 text-xs font-semibold ' . $tone . '">' . e(str_replace('_', ' ', $s)) . '</span>';
};
$filterLinks = function (string $tab, string $param, array $options, string $current) {
    $h = '<div class="mb-5 flex flex-wrap gap-2">';
    foreach ($options as $k => $label) {
        $h .= '<a href="' . url('/admin', ['tab' => $tab, $param => $k]) . '" class="rounded-control border px-3.5 py-1.5 text-sm no-underline ' . ($current === $k ? 'border-primary bg-primary text-primary-foreground' : 'border-border text-foreground') . '">' . e($label) . '</a>';
    }
    return $h . '</div>';
};
?>
<section class="px-4 pt-10 sm:px-8 lg:px-12 lg:pt-14">
  <p class="eyebrow">Site admin</p>
  <h1 class="mb-8 mt-2 font-display text-4xl font-extrabold tracking-tight">Manage IdleWheels</h1>
  <div class="grid gap-8 lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-12">
    <nav class="flex gap-2 overflow-x-auto lg:flex-col" aria-label="Admin sections">
      <?php foreach ($tabs as $k => $label): ?><a href="<?= url('/admin', ['tab' => $k]) ?>" class="tab <?= $tab === $k ? 'tab-active' : '' ?>"><?= e($label) ?></a><?php endforeach; ?>
    </nav>
    <div class="min-w-0">

<?php if ($tab === 'overview'): $s = $stats; ?>
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <?php foreach ([
        ['Host applications', $s['pending_hosts'], 'to review', 'hosts'],
        ['Car listings', $s['pending_cars'], 'to review · ' . $s['live_cars'] . ' live', 'cars'],
        ['Booking requests', $s['awaiting_host'], 'waiting for hosts · ' . $s['upcoming'] . ' confirmed upcoming', 'bookings'],
        ['Refunds due', $s['refunds'], 'declined or cancelled after payment', 'bookings'],
        ['Messages', $s['messages'], 'unanswered', 'messages'],
        ['Users', $s['users'], 'registered accounts', 'users'],
    ] as [$l, $v, $h, $t]): ?>
      <a href="<?= url('/admin', ['tab' => $t] + ($l === 'Refunds due' ? ['filter' => 'refunds'] : [])) ?>" class="stat no-underline hover:border-primary"><p class="stat-label"><?= e($l) ?></p><p class="stat-value text-foreground"><?= (int) $v ?></p><p class="stat-hint"><?= e($h) ?></p></a>
    <?php endforeach; ?>
  </div>

<?php elseif ($tab === 'hosts'): ?>
  <?php if (!$hosts): ?><p class="empty">No host applications yet.</p><?php endif; ?>
  <ul class="space-y-3">
    <?php foreach ($hosts as $h): ?>
      <li class="rounded-lg border border-border bg-background p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="font-display text-lg font-semibold"><?= e($h['display_name']) ?></p>
            <p class="text-sm text-muted-foreground"><?= e($h['email']) ?> · <?= e($h['phone']) ?> · <?= e($h['city']) ?></p>
            <p class="mt-1 text-xs text-muted-foreground">Applied <?= e(fmt_date($h['created_at'], 'j M Y')) ?> · <?= (int) $h['car_count'] ?> cars · Payout: <?= e($h['payout_method']) ?></p>
            <?php if ($h['about']): ?><p class="mt-2 max-w-2xl text-sm"><?= e($h['about']) ?></p><?php endif; ?>
            <?php if ($h['admin_note']): ?><p class="mt-1 text-xs text-accent">Note: <?= e($h['admin_note']) ?></p><?php endif; ?>
          </div>
          <?= $chip($h['status']) ?>
        </div>
        <form method="post" action="<?= url('/admin/hosts/' . $h['id']) ?>" class="mt-4 flex flex-wrap items-center gap-2 border-t border-border pt-4">
          <?= csrf_field() ?>
          <?php if ($h['status'] !== 'approved'): ?><button name="status" value="approved" class="btn-primary btn-sm">Approve</button><?php endif; ?>
          <input name="note" maxlength="500" placeholder="Reason (shown to the applicant)" class="field max-w-xs">
          <?php if ($h['status'] !== 'rejected'): ?><button name="status" value="rejected" class="btn-ghost btn-sm"><?= $h['status'] === 'approved' ? 'Revoke' : 'Reject' ?></button><?php endif; ?>
        </form>
      </li>
    <?php endforeach; ?>
  </ul>

<?php elseif ($tab === 'cars'): ?>
  <?= $filterLinks('cars', 'status', ['pending' => 'To review', 'approved' => 'Live', 'rejected' => 'Rejected', 'hidden' => 'Hidden', 'all' => 'All'], $filter) ?>
  <?php if (!$cars): ?><p class="empty">Nothing here.</p><?php endif; ?>
  <ul class="space-y-3">
    <?php foreach ($cars as $c): ?>
      <li class="rounded-lg border border-border bg-background p-5">
        <div class="flex flex-wrap gap-5">
          <div class="h-24 w-36 shrink-0 overflow-hidden rounded-md bg-secondary"><?php if ($c['photo']): ?><img src="<?= e(photo_url($c['photo'])) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?></div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-start justify-between gap-2"><p class="font-display text-lg font-semibold"><?= e($c['title']) ?></p><?= $chip($c['status']) ?></div>
            <p class="text-sm text-muted-foreground"><?= e(implode(' · ', array_filter([$c['category_name'], $c['seats'] . ' seats', $c['transmission'], $c['fuel'], $c['location']]))) ?></p>
            <p class="mt-1 text-sm">Host: <span class="font-semibold"><?= e($c['host_name']) ?></span> · <?= e(money_short($c['price_per_day'])) ?> / day · <?= (int) $c['min_days'] ?>–<?= (int) $c['max_days'] ?> days<?= $c['requires_approval'] ? ' · host approves' : ' · instant book' ?></p>
            <?php if ($c['is_featured']): ?><p class="mt-1 text-xs font-semibold text-primary">Featured until <?= e(fmt_date($c['featured_until'], 'j M Y, H:i')) ?></p><?php endif; ?>
            <?php if ($c['admin_note']): ?><p class="mt-1 text-xs text-accent">Note: <?= e($c['admin_note']) ?></p><?php endif; ?>
          </div>
        </div>
        <details class="mt-3"><summary class="cursor-pointer text-sm text-muted-foreground">Description</summary><p class="mt-2 max-w-3xl whitespace-pre-line text-sm"><?= e($c['description']) ?></p></details>
        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-border pt-4">
          <form method="post" action="<?= url('/admin/cars/' . $c['id']) ?>" class="flex flex-wrap items-center gap-2">
            <?= csrf_field() ?>
            <?php if ($c['status'] !== 'approved'): ?><button name="status" value="approved" class="btn-primary btn-sm">Approve and publish</button><?php endif; ?>
            <input name="note" maxlength="500" placeholder="Reason (shown to the host)" class="field max-w-xs">
            <?php if ($c['status'] !== 'rejected'): ?><button name="status" value="rejected" class="btn-ghost btn-sm">Reject</button><?php endif; ?>
            <?php if ($c['status'] === 'approved'): ?><button name="status" value="hidden" class="btn-ghost btn-sm">Hide from site</button><?php endif; ?>
          </form>
          <?php if ($c['status'] === 'approved'): ?>
            <form method="post" action="<?= url('/admin/cars/' . $c['id'] . '/feature') ?>" class="flex items-center gap-2">
              <?= csrf_field() ?>
              <input type="number" name="days" min="1" max="90" value="7" class="field w-20" aria-label="Days">
              <button name="action" value="feature" class="btn-ghost btn-sm">Feature free</button>
              <?php if ($c['is_featured']): ?><button name="action" value="end" class="btn-ghost btn-sm">End featuring</button><?php endif; ?>
            </form>
            <a href="<?= url('/cars/' . $c['slug']) ?>" class="text-sm font-semibold text-primary">View</a>
          <?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

<?php elseif ($tab === 'bookings'): ?>
  <?= $filterLinks('bookings', 'filter', ['upcoming' => 'Upcoming', 'awaiting_host' => 'Awaiting host', 'refunds' => 'Refunds due', 'all' => 'All paid', 'unpaid' => 'Unpaid'], $filter) ?>
  <?php if (!$bookings): ?><p class="empty">No bookings here.</p><?php else: ?>
  <div class="overflow-x-auto rounded-lg border border-border">
    <table class="table">
      <thead><tr><th>Booking</th><th>Dates</th><th>Renter</th><th>Status</th><th class="text-right">Total</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($bookings as $b): $st = Booking::displayStatus($b); ?>
          <tr>
            <td><a href="<?= url('/bookings/' . $b['ref']) ?>" class="font-semibold text-foreground"><?= e($b['car_title']) ?></a><span class="block text-xs text-muted-foreground"><?= e($b['ref']) ?> · host <?= e($b['host_name']) ?></span></td>
            <td><?= e(fmt_date($b['start_date'], 'j M')) ?> – <?= e(fmt_date($b['end_date'], 'j M Y')) ?></td>
            <td><?= e($b['renter_name']) ?><span class="block text-xs text-muted-foreground"><?= e($b['renter_phone']) ?></span></td>
            <td><?= $chip($st) ?><?php if ($b['payment_status'] !== 'paid'): ?><span class="block text-xs text-muted-foreground"><?= e(str_replace('_', ' ', $b['payment_status'])) ?></span><?php endif; ?></td>
            <td class="text-right font-semibold"><?= e(money($b['total'])) ?></td>
            <td class="text-right">
              <form method="post" action="<?= url('/admin/bookings/' . $b['ref'], ['filter' => $filter]) ?>" class="inline-flex flex-wrap justify-end gap-1">
                <?= csrf_field() ?>
                <?php if ($b['status'] === 'awaiting_host'): ?><button name="action" value="confirm" class="btn-primary btn-sm">Confirm</button><button name="action" value="decline" class="btn-ghost btn-sm">Decline</button><?php endif; ?>
                <?php if (in_array($b['status'], ['awaiting_host', 'confirmed'], true) && $b['end_date'] > date('Y-m-d')): ?><button name="action" value="cancel" class="btn-ghost btn-sm" onclick="return confirm('Cancel this booking?')">Cancel</button><?php endif; ?>
                <?php if ($b['payment_status'] === 'refund_due'): ?><button name="action" value="refunded" class="btn-ghost btn-sm" title="Refund it in your Paystack dashboard first">Mark refunded</button><?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="mt-3 text-xs text-muted-foreground">Refunds are made from your Paystack dashboard (Transactions → Refund). Then mark the booking refunded here.</p>
  <?php endif; ?>

<?php elseif ($tab === 'users'): ?>
  <form method="get" action="<?= url('/admin') ?>" class="mb-4 flex gap-2"><input type="hidden" name="tab" value="users"><input name="q" value="<?= e($q) ?>" placeholder="Search name or email" class="field max-w-sm"><button class="btn-ghost">Search</button></form>
  <div class="overflow-x-auto rounded-lg border border-border">
    <table class="table">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Bookings</th><th>Joined</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="font-semibold"><?= e($u['name']) ?><?php if ($u['status'] === 'blocked'): ?> <span class="text-xs text-destructive">blocked</span><?php endif; ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= $u['role'] === 'admin' ? 'Admin' : ($u['host_status'] === 'approved' ? 'Host' : ($u['host_status'] ? 'Host (' . e($u['host_status']) . ')' : 'Renter')) ?></td>
            <td><?= (int) $u['booking_count'] ?></td>
            <td><?= e(fmt_date($u['created_at'], 'j M Y')) ?></td>
            <td class="text-right">
              <?php if ((int) $u['id'] !== (int) $admin['id']): ?>
                <form method="post" action="<?= url('/admin/users/' . $u['id']) ?>" class="inline-flex gap-1"><?= csrf_field() ?>
                  <button name="action" value="<?= $u['status'] === 'blocked' ? 'unblock' : 'block' ?>" class="btn-ghost btn-sm"><?= $u['status'] === 'blocked' ? 'Unblock' : 'Block' ?></button>
                  <button name="action" value="<?= $u['role'] === 'admin' ? 'remove_admin' : 'make_admin' ?>" class="btn-ghost btn-sm" onclick="return confirm('Change this user\'s admin access?')"><?= $u['role'] === 'admin' ? 'Remove admin' : 'Make admin' ?></button>
                </form>
              <?php else: ?><span class="text-xs text-muted-foreground">You</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'revenue'): $r = $revenue; ?>
  <?= $filterLinks('revenue', 'days', ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', '365' => 'Last year'], (string) $r['days']) ?>
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="stat border-primary"><p class="stat-label">Platform revenue</p><p class="stat-value"><?= e(money($r['platform'])) ?></p><p class="stat-hint">Commission + service fees + featured</p></div>
    <div class="stat"><p class="stat-label">Booking commission</p><p class="stat-value"><?= e(money($r['commission'])) ?></p><p class="stat-hint">From <?= (int) $r['bookings'] ?> paid bookings</p></div>
    <div class="stat"><p class="stat-label">Renter service fees</p><p class="stat-value"><?= e(money($r['service_fees'])) ?></p><p class="stat-hint">Added at checkout</p></div>
    <div class="stat"><p class="stat-label">Featured listings</p><p class="stat-value"><?= e(money($r['featured'])) ?></p><p class="stat-hint"><?= (int) $r['featured_count'] ?> paid promotions</p></div>
  </div>
  <div class="mt-4 grid gap-4 sm:grid-cols-3">
    <div class="stat"><p class="stat-label">Paid by renters</p><p class="stat-value"><?= e(money($r['gross'])) ?></p></div>
    <div class="stat"><p class="stat-label">Extras sold</p><p class="stat-value"><?= e(money($r['addons'])) ?></p><p class="stat-hint">Part of host earnings</p></div>
    <div class="stat"><p class="stat-label">Owed to hosts</p><p class="stat-value"><?= e(money($r['host_earnings'])) ?></p><p class="stat-hint">After commission</p></div>
  </div>
  <?php if ($r['rows']): ?>
    <div class="mt-8 overflow-x-auto rounded-lg border border-border">
      <table class="table">
        <thead><tr><th>Booking</th><th class="text-right">Rental</th><th class="text-right">Extras</th><th class="text-right">Service fee</th><th class="text-right">Commission</th><th class="text-right">Host earns</th><th class="text-right">Total</th></tr></thead>
        <tbody>
          <?php foreach ($r['rows'] as $row): ?>
            <tr><td><a href="<?= url('/bookings/' . $row['ref']) ?>" class="font-semibold text-foreground"><?= e($row['car_title']) ?></a><span class="block text-xs text-muted-foreground"><?= e($row['ref']) ?> · <?= e(fmt_date($row['start_date'], 'j M Y')) ?></span></td>
              <td class="text-right"><?= e(money($row['rental_amount'])) ?></td><td class="text-right"><?= e(money($row['addons_amount'])) ?></td>
              <td class="text-right"><?= e(money($row['service_fee'])) ?></td><td class="text-right"><?= e(money($row['commission'])) ?></td>
              <td class="text-right"><?= e(money($row['host_earnings'])) ?></td><td class="text-right font-semibold"><?= e(money($row['total'])) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?><p class="empty mt-8">No paid bookings in this period yet.</p><?php endif; ?>

<?php elseif ($tab === 'addons'): ?>
  <p class="mb-4 text-sm text-muted-foreground">Extras renters can add on every car's page. They're charged with the rental and count towards the host's earnings (before commission).</p>
  <div class="space-y-3">
    <?php foreach (array_merge($addons, [null]) as $a): ?>
      <form method="post" action="<?= url('/admin/addons') ?>" class="grid gap-3 rounded-lg border <?= $a ? 'border-border' : 'border-dashed border-primary' ?> bg-background p-4 sm:grid-cols-[2fr_2fr_1fr_1fr_auto] sm:items-end">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $a ? (int) $a['id'] : '' ?>">
        <label><span class="field-label"><?= $a ? 'Name' : 'New extra' ?></span><input name="name" required maxlength="100" value="<?= e($a['name'] ?? '') ?>" class="field"></label>
        <label><span class="field-label">Description</span><input name="description" maxlength="200" value="<?= e($a['description'] ?? '') ?>" class="field"></label>
        <label><span class="field-label">Price (<?= e(currency()) ?>)</span><input type="number" name="price" required min="0" step="0.5" value="<?= e($a['price'] ?? '') ?>" class="field"></label>
        <label title="1 = a yes/no tick box for renters"><span class="field-label">Max qty (1 = yes/no)</span><input type="number" name="max_quantity" required min="1" max="60" value="<?= e($a['max_quantity'] ?? 1) ?>" class="field"></label>
        <div class="flex flex-wrap items-center gap-3 sm:col-span-5">
          <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="per_day" value="1" <?= !empty($a['per_day']) ? 'checked' : '' ?>> Priced per day</label>
          <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="active" value="1" <?= ($a === null || $a['active']) ? 'checked' : '' ?>> Offered to renters</label>
          <label class="flex items-center gap-2 text-sm">Order <input type="number" name="sort_order" value="<?= e($a['sort_order'] ?? 100) ?>" class="field w-20"></label>
          <button class="btn-primary btn-sm ml-auto"><?= $a ? 'Save' : 'Add extra' ?></button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>

<?php elseif ($tab === 'categories'): ?>
  <p class="mb-4 text-sm text-muted-foreground">Car types shown on the home page and in the search filter.</p>
  <div class="space-y-3">
    <?php foreach (array_merge($categories, [null]) as $c): ?>
      <div class="flex flex-wrap items-end gap-3 rounded-lg border <?= $c ? 'border-border' : 'border-dashed border-primary' ?> bg-background p-4">
        <div class="h-14 w-20 shrink-0 overflow-hidden rounded-sm bg-secondary"><?php if (!empty($c['image'])): ?><img src="<?= e(photo_url($c['image'])) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?></div>
        <form method="post" action="<?= url('/admin/categories') ?>" enctype="multipart/form-data" class="grid flex-1 gap-3 sm:grid-cols-[1fr_2fr_6rem_auto] sm:items-end">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $c ? (int) $c['id'] : '' ?>">
          <label><span class="field-label"><?= $c ? 'Name' : 'New category' ?></span><input name="name" required maxlength="60" value="<?= e($c['name'] ?? '') ?>" class="field"></label>
          <label><span class="field-label">Short description</span><input name="blurb" maxlength="150" value="<?= e($c['blurb'] ?? '') ?>" class="field"></label>
          <label><span class="field-label">Order</span><input type="number" name="sort_order" value="<?= e($c['sort_order'] ?? 50) ?>" class="field"></label>
          <label class="text-sm sm:col-span-3"><span class="field-label">Image</span><input type="file" name="image[]" accept="image/jpeg,image/png,image/webp" class="text-sm"></label>
          <button class="btn-primary btn-sm"><?= $c ? 'Save' : 'Add' ?></button>
        </form>
        <?php if ($c): ?>
          <form method="post" action="<?= url('/admin/categories/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Delete the <?= e($c['name']) ?> category?')"><?= csrf_field() ?><button class="text-sm text-muted-foreground hover:text-destructive">Delete</button></form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

<?php elseif ($tab === 'reviews'): ?>
  <?php if (!$reviews): ?><p class="empty">No reviews yet.</p><?php endif; ?>
  <ul class="space-y-3">
    <?php foreach ($reviews as $rv): ?>
      <li class="rounded-lg border border-border bg-background p-5 <?= $rv['hidden'] ? 'opacity-60' : '' ?>">
        <div class="flex flex-wrap justify-between gap-2">
          <p class="text-sm"><span class="text-primary"><?= str_repeat('★', (int) $rv['rating']) ?></span> <span class="font-semibold"><?= e(Review::shortName($rv['author'])) ?></span> on <a href="<?= url('/cars/' . $rv['car_slug']) ?>" class="text-primary"><?= e($rv['car_title']) ?></a><?= $rv['hidden'] ? ' · hidden' : '' ?></p>
          <span class="text-xs text-muted-foreground"><?= e(fmt_date($rv['created_at'], 'j M Y')) ?></span>
        </div>
        <p class="mt-2 text-sm"><?= e($rv['comment']) ?></p>
        <form method="post" action="<?= url('/admin/reviews/' . $rv['id']) ?>" class="mt-3"><?= csrf_field() ?><button name="action" value="<?= $rv['hidden'] ? 'show' : 'hide' ?>" class="text-sm text-muted-foreground hover:text-destructive"><?= $rv['hidden'] ? 'Show on site' : 'Hide from site' ?></button></form>
      </li>
    <?php endforeach; ?>
  </ul>

<?php elseif ($tab === 'messages'): ?>
  <?php if (!$messages): ?><p class="empty">No messages yet.</p><?php endif; ?>
  <ul class="space-y-3">
    <?php foreach ($messages as $m): ?>
      <li class="rounded-lg border border-border bg-background p-5 <?= $m['handled'] ? 'opacity-60' : '' ?>">
        <div class="flex flex-wrap justify-between gap-2">
          <p class="text-sm"><span class="font-semibold"><?= e($m['name']) ?></span> · <a href="mailto:<?= e($m['email']) ?>" class="text-primary"><?= e($m['email']) ?></a><?= $m['phone'] ? ' · ' . e($m['phone']) : '' ?> · <?= e($m['topic']) ?><?= $m['booking_ref'] ? ' · booking ' . e($m['booking_ref']) : '' ?></p>
          <span class="text-xs text-muted-foreground"><?= e(fmt_date($m['created_at'], 'j M Y, H:i')) ?></span>
        </div>
        <p class="mt-2 whitespace-pre-line text-sm"><?= e($m['message']) ?></p>
        <?php if (!$m['handled']): ?><form method="post" action="<?= url('/admin/messages/' . $m['id']) ?>" class="mt-3"><?= csrf_field() ?><button class="btn-ghost btn-sm">Mark as handled</button></form><?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>

<?php elseif ($tab === 'settings'): $st = $settings; ?>
  <form method="post" action="<?= url('/admin/settings') ?>" class="grid max-w-xl gap-5 rounded-lg border border-border bg-background p-6">
    <?= csrf_field() ?>
    <p class="font-display text-xl font-semibold">Revenue model</p>
    <label><span class="field-label">Booking commission (% of rental + extras, taken from host earnings)</span><input type="number" name="commission_percent" required min="0" max="50" step="0.5" value="<?= e($st['commission_percent'] ?? 15) ?>" class="field"></label>
    <label><span class="field-label">Renter service fee (% added at checkout)</span><input type="number" name="service_fee_percent" required min="0" max="50" step="0.5" value="<?= e($st['service_fee_percent'] ?? 7) ?>" class="field"></label>
    <label><span class="field-label">Service fee name shown to renters</span><input name="service_fee_name" required maxlength="60" value="<?= e($st['service_fee_name'] ?? 'Service fee') ?>" class="field"></label>
    <div class="grid grid-cols-2 gap-4">
      <label><span class="field-label">Featured listing price (<?= e(currency()) ?>)</span><input type="number" name="featured_price" required min="0" step="1" value="<?= e($st['featured_price'] ?? 50) ?>" class="field"></label>
      <label><span class="field-label">Featured listing days</span><input type="number" name="featured_days" required min="1" max="90" value="<?= e($st['featured_days'] ?? 7) ?>" class="field"></label>
    </div>
    <p class="text-xs text-muted-foreground">New rates apply to new bookings. Existing bookings keep the rates they were made with.</p>
    <button class="btn-primary justify-self-start">Save settings</button>
  </form>
<?php endif; ?>

    </div>
  </div>
</section>
