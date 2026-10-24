<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Service;
use App\Models\ServiceVariant;

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
     * Get raw cart items
     */
    public static function getRaw(): array
    {
        self::init();
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Build standard item key for session cart
     */
    public static function buildKey(int $serviceId, array $variantIds = []): string
    {
        $cleanVariants = array_values(array_filter(array_map('intval', $variantIds)));
        sort($cleanVariants);
        if (empty($cleanVariants)) {
            return (string)$serviceId;
        }
        return $serviceId . '_v' . implode('_', $cleanVariants);
    }

    /**
     * Get full cart with database-verified services, quantities, variants, prices and taxes
     */
    public static function getDetails(): array
    {
        self::init();
        $raw = $_SESSION[self::SESSION_KEY];
        $items = [];
        $subtotal = 0.0;
        $itemCount = 0;

        foreach ($raw as $key => $entry) {
            $serviceId = 0;
            $qty = 0;
            $variantIds = [];

            if (is_array($entry)) {
                $serviceId = (int)($entry['service_id'] ?? 0);
                $qty = (int)($entry['quantity'] ?? 0);
                $variantIds = (array)($entry['variant_ids'] ?? []);
            } else {
                $serviceId = (int)$key;
                $qty = (int)$entry;
            }

            if ($qty <= 0 || $serviceId <= 0) {
                unset($_SESSION[self::SESSION_KEY][$key]);
                continue;
            }

            $service = Service::find($serviceId);
            if (!$service || empty($service['is_active']) || !empty($service['is_coming_soon'])) {
                // Remove inactive, deleted, or coming soon service automatically
                unset($_SESSION[self::SESSION_KEY][$key]);
                continue;
            }

            $basePrice = (float)$service['starting_price'];
            $name = (string)$service['name'];
            $duration = (int)($service['duration_minutes'] ?? 60);
            $variantItems = [];
            $variantSuffix = '';

            if (!empty($variantIds)) {
                $calc = ServiceVariant::calculatePrice($serviceId, $variantIds, $basePrice);
                $unitPrice = (float)$calc['total_price'];
                $variantSuffix = (string)$calc['title_suffix'];
                $name .= $variantSuffix;
                $duration = (int)$calc['duration'];
                $variantItems = $calc['variant_items'] ?? [];
            } else {
                $unitPrice = $basePrice;
            }

            $lineTotal = $unitPrice * $qty;
            $subtotal += $lineTotal;
            $itemCount += $qty;

            $items[] = [
                'cart_key'       => (string)$key,
                'service_id'     => (int)$service['id'],
                'name'           => $name,
                'base_name'      => $service['name'],
                'variant_suffix' => $variantSuffix,
                'slug'           => $service['slug'],
                'image'          => Service::resolveImage($service['slug'] ?? '', $service['image'] ?? null),
                'duration'       => $duration,
                'unit_price'     => $unitPrice,
                'quantity'       => $qty,
                'line_total'     => $lineTotal,
                'variant_ids'    => $variantIds,
                'variant_items'  => $variantItems,
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

    public static function add(int $serviceId, int $quantity = 1, array $variantIds = []): array
    {
        self::init();
        $service = Service::find($serviceId);
        if (!$service || empty($service['is_active'])) {
            return ['success' => false, 'error' => 'Service unavailable'];
        }

        if (!empty($service['is_coming_soon'])) {
            return [
                'success'        => false,
                'error'          => "Service '{$service['name']}' is coming soon and cannot be added to cart yet.",
                'is_coming_soon' => true,
            ];
        }

        $cleanVariants = array_values(array_filter(array_map('intval', $variantIds)));
        sort($cleanVariants);
        $key = self::buildKey($serviceId, $cleanVariants);

        if (isset($_SESSION[self::SESSION_KEY][$key])) {
            if (is_array($_SESSION[self::SESSION_KEY][$key])) {
                $_SESSION[self::SESSION_KEY][$key]['quantity'] += $quantity;
            } else {
                $_SESSION[self::SESSION_KEY][$key] += $quantity;
            }
        } else {
            $_SESSION[self::SESSION_KEY][$key] = [
                'service_id'  => $serviceId,
                'quantity'    => $quantity,
                'variant_ids' => $cleanVariants,
            ];
        }

        return ['success' => true, 'cart' => self::getDetails()];
    }

    public static function update($keyOrId, int $quantity): array
    {
        self::init();
        $key = (string)$keyOrId;

        if ($quantity <= 0) {
            unset($_SESSION[self::SESSION_KEY][$key]);
            if (is_numeric($keyOrId)) {
                unset($_SESSION[self::SESSION_KEY][(int)$keyOrId]);
            }
        } else {
            if (isset($_SESSION[self::SESSION_KEY][$key])) {
                if (is_array($_SESSION[self::SESSION_KEY][$key])) {
                    $_SESSION[self::SESSION_KEY][$key]['quantity'] = $quantity;
                } else {
                    $_SESSION[self::SESSION_KEY][$key] = $quantity;
                }
            } elseif (is_numeric($keyOrId) && isset($_SESSION[self::SESSION_KEY][(int)$keyOrId])) {
                $_SESSION[self::SESSION_KEY][(int)$keyOrId] = $quantity;
            }
        }

        return ['success' => true, 'cart' => self::getDetails()];
    }

    public static function remove($keyOrId): array
    {
        self::init();
        $key = (string)$keyOrId;
        unset($_SESSION[self::SESSION_KEY][$key]);
        if (is_numeric($keyOrId)) {
            unset($_SESSION[self::SESSION_KEY][(int)$keyOrId]);
        }
        return ['success' => true, 'cart' => self::getDetails()];
    }

    public static function clear(): void
    {
        self::init();
        $_SESSION[self::SESSION_KEY] = [];
    }
}
