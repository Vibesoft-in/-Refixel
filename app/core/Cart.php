<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Service;

class Cart
{
    protected const SESSION_KEY = '_REFIXEL_cart';
    protected const TAX_RATE = 0.18; // 18% GST

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    /**
     * Get raw cart items (service_id => quantity)
     */
    public static function getRaw(): array
    {
        self::init();
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Get full cart with database-verified services, quantities, prices and taxes
     */
    public static function getDetails(): array
    {
        self::init();
        $raw = $_SESSION[self::SESSION_KEY];
        $items = [];
        $subtotal = 0.0;
        $itemCount = 0;

        foreach ($raw as $serviceId => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;

            $service = Service::find((int)$serviceId);
            if (!$service || empty($service['is_active'])) {
                // Remove inactive or deleted service automatically
                unset($_SESSION[self::SESSION_KEY][$serviceId]);
                continue;
            }

            $unitPrice = (float)$service['starting_price'];
            $lineTotal = $unitPrice * $qty;
            $subtotal += $lineTotal;
            $itemCount += $qty;

            $items[] = [
                'service_id'   => (int)$service['id'],
                'name'         => $service['name'],
                'slug'         => $service['slug'],
                'image'        => $service['image'] ?? 'Full-home-clean.jpg',
                'duration'     => (int)($service['duration_minutes'] ?? 60),
                'unit_price'   => $unitPrice,
                'quantity'     => $qty,
                'line_total'   => $lineTotal,
            ];
        }

        $tax = round($subtotal * self::TAX_RATE, 2);
        $total = $subtotal; // Starting prices in REFIXEL are GST-inclusive or transparently calculated

        return [
            'items'      => $items,
            'count'      => $itemCount,
            'subtotal'   => $subtotal,
            'tax'        => $tax,
            'total'      => $total,
            'is_empty'   => empty($items),
        ];
    }

    public static function add(int $serviceId, int $quantity = 1): array
    {
        self::init();
        $service = Service::find($serviceId);
        if (!$service || empty($service['is_active'])) {
            return ['success' => false, 'error' => 'Service unavailable'];
        }

        if (isset($_SESSION[self::SESSION_KEY][$serviceId])) {
            $_SESSION[self::SESSION_KEY][$serviceId] += $quantity;
        } else {
            $_SESSION[self::SESSION_KEY][$serviceId] = $quantity;
        }

        return ['success' => true, 'cart' => self::getDetails()];
    }

    public static function update(int $serviceId, int $quantity): array
    {
        self::init();
        if ($quantity <= 0) {
            unset($_SESSION[self::SESSION_KEY][$serviceId]);
        } else {
            $service = Service::find($serviceId);
            if ($service && !empty($service['is_active'])) {
                $_SESSION[self::SESSION_KEY][$serviceId] = $quantity;
            }
        }

        return ['success' => true, 'cart' => self::getDetails()];
    }

    public static function remove(int $serviceId): array
    {
        self::init();
        unset($_SESSION[self::SESSION_KEY][$serviceId]);
        return ['success' => true, 'cart' => self::getDetails()];
    }

    public static function clear(): void
    {
        self::init();
        $_SESSION[self::SESSION_KEY] = [];
    }
}

