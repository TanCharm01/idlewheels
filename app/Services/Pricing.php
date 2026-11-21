<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Addon;
use App\Models\Setting;

class Pricing
{
    public static function days(string $start, string $end): int
    {
        return (int) round((strtotime($end) - strtotime($start)) / 86400);
    }

    public static function quote(array $car, string $start, string $end, array $quantities = []): array
    {
        $days = self::days($start, $end);
        $rental = round($days * (float) $car['price_per_day'], 2);
        $lines = [];
        foreach (Addon::active() as $a) {
            $q = (int) ($quantities[$a['id']] ?? 0);
            if ($q <= 0) {
                continue;
            }
            if ((int) $a['max_quantity'] === 1) {
                // yes/no extra: a per-day one is charged for every day of the rental
                $q = $a['per_day'] ? max(1, $days) : 1;
            } else {
                $q = min($q, (int) $a['max_quantity'], $a['per_day'] ? max(1, $days) : (int) $a['max_quantity']);
            }
            $lines[] = [
                'addon_id' => (int) $a['id'],
                'name' => $a['name'] . ($a['per_day'] ? ' (per day)' : ''),
                'unit_price' => (float) $a['price'],
                'quantity' => $q,
                'line_total' => round($q * (float) $a['price'], 2),
            ];
        }
        $extras = round(array_sum(array_column($lines, 'line_total')), 2);
        $subtotal = round($rental + $extras, 2);
        $feePct = Setting::num('service_fee_percent', 7);
        $comPct = Setting::num('commission_percent', 15);
        $fee = round($subtotal * $feePct / 100, 2);
        $commission = round($subtotal * $comPct / 100, 2);
        return [
            'days' => $days,
            'price_per_day' => (float) $car['price_per_day'],
            'rental' => $rental,
            'addons' => $lines,
            'addons_total' => $extras,
            'subtotal' => $subtotal,
            'service_fee_percent' => $feePct,
            'service_fee' => $fee,
            'commission_percent' => $comPct,
            'commission' => $commission,
            'host_earnings' => round($subtotal - $commission, 2),
            'total' => round($subtotal + $fee, 2),
        ];
    }
}
