<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Booking extends Model
{
    protected static string $table = 'bookings';

    public static function generateBookingNo(): string
    {
        return 'BK-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public static function generateGuestToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function findByGuestToken(string $token): ?array
    {
        if (empty($token) || strlen($token) < 32) {
            return null;
        }
        return Database::fetchOne(
            "SELECT b.*, s.name as service_name 
             FROM bookings b 
             LEFT JOIN services s ON b.service_id = s.id 
             WHERE b.guest_token = :tok 
             LIMIT 1",
            ['tok' => $token]
        );
    }

    public static function findByCustomer(int $customerId): array
    {
        return Database::fetchAll(
            "SELECT b.*, s.name as service_name, s.starting_price 
             FROM bookings b 
             JOIN services s ON b.service_id = s.id 
             WHERE b.customer_id = :cid 
             ORDER BY b.created_at DESC",
            ['cid' => $customerId]
        );
    }

    public static function findWithDetails(int $id): ?array
    {
        $row = Database::fetchOne(
            "SELECT b.*, s.category_id, s.name as service_name, s.starting_price, c.name as category_name,
                    u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                    j.id as job_id, j.status as job_status, j.staff_id, st.name as staff_name,
                    st.phone as staff_phone, st.email as staff_email,
                    (SELECT p.status FROM payments p WHERE p.booking_id = b.id AND p.status = 'paid' ORDER BY p.id DESC LIMIT 1) as payment_paid_status,
                    (SELECT p.amount FROM payments p WHERE p.booking_id = b.id AND p.status = 'paid' ORDER BY p.id DESC LIMIT 1) as payment_paid_amount
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             JOIN categories c ON s.category_id = c.id
             LEFT JOIN users u ON b.customer_id = u.id
             LEFT JOIN jobs j ON b.id = j.booking_id
             LEFT JOIN users st ON j.staff_id = st.id
             WHERE b.id = :id
             LIMIT 1",
            ['id' => $id]
        );

        if ($row) {
            $row['items'] = self::getItems($id, $row);
            $row['addons'] = array_values(array_filter($row['items'], fn($i) => ($i['item_type'] ?? '') === 'addon'));
        }

        return $row;
    }

    /**
     * Self-healing migration for items_json column and booking_items table.
     */
    public static function ensureItemsSchema(): void
    {
        static $ensured = false;
        if ($ensured) return;
        $ensured = true;

        try {
            Database::execute("
            CREATE TABLE IF NOT EXISTS `booking_items` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `booking_id` INT UNSIGNED NOT NULL,
              `service_id` INT UNSIGNED NULL,
              `item_type` ENUM('service', 'addon') NOT NULL DEFAULT 'service',
              `name` VARCHAR(150) NOT NULL,
              `quantity` INT NOT NULL DEFAULT 1,
              `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
              `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
              `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_booking_id` (`booking_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (\Throwable $e) {}

        try {
            $cols = Database::fetchAll("SHOW COLUMNS FROM bookings");
            $fields = array_column($cols, 'Field');
            if (!in_array('items_json', $fields, true)) {
                try {
                    Database::execute("ALTER TABLE bookings ADD COLUMN items_json TEXT NULL AFTER issue_details");
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Record itemized line items (main service + add-ons) for a booking.
     */
    public static function recordItems(int $bookingId, array $items): void
    {
        if (empty($items)) {
            return;
        }

        self::ensureItemsSchema();

        $formatted = [];
        foreach ($items as $item) {
            $sid = !empty($item['service_id']) ? (int)$item['service_id'] : null;
            $name = trim((string)($item['name'] ?? 'Service Item'));
            $type = ($item['item_type'] ?? '') === 'addon' ? 'addon' : 'service';
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $uPrice = (float)($item['unit_price'] ?? 0);
            $tPrice = (float)($item['total_price'] ?? ($uPrice * $qty));
            $badge = trim((string)($item['badge'] ?? ''));

            $formatted[] = [
                'service_id'  => $sid,
                'name'        => $name,
                'item_type'   => $type,
                'quantity'    => $qty,
                'unit_price'  => $uPrice,
                'total_price' => $tPrice,
                'badge'       => $badge,
            ];

            try {
                Database::execute(
                    "INSERT INTO booking_items (booking_id, service_id, item_type, name, quantity, unit_price, total_price, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                    [$bookingId, $sid, $type, $name, $qty, $uPrice, $tPrice]
                );
            } catch (\Throwable $e) {}
        }

        // Update items_json on bookings table for ultra-fast single-query access
        try {
            $json = json_encode($formatted, JSON_UNESCAPED_UNICODE);
            Database::execute("UPDATE bookings SET items_json = ? WHERE id = ?", [$json, $bookingId]);
        } catch (\Throwable $e) {}
    }

    /**
     * Retrieve all itemized services and add-ons for a booking.
     * Features self-healing fallback for legacy bookings where total_amount > starting_price.
     */
    public static function getItems(int $bookingId, ?array $bookingContext = null): array
    {
        self::ensureItemsSchema();

        // 1. Check items_json from context or database
        $json = $bookingContext['items_json'] ?? null;
        if (empty($json) && $bookingId > 0) {
            try {
                $row = Database::fetchOne("SELECT items_json FROM bookings WHERE id = :id LIMIT 1", ['id' => $bookingId]);
                $json = $row['items_json'] ?? null;
            } catch (\Throwable $e) {}
        }

        if (!empty($json)) {
            $decoded = json_decode((string)$json, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        // 2. Check booking_items table
        if ($bookingId > 0) {
            try {
                $dbItems = Database::fetchAll(
                    "SELECT service_id, name, item_type, quantity, unit_price, total_price
                     FROM booking_items WHERE booking_id = :bid ORDER BY id ASC",
                    ['bid' => $bookingId]
                );
                if (!empty($dbItems)) {
                    return $dbItems;
                }
            } catch (\Throwable $e) {}
        }

        // 3. Fallback / legacy auto-resolution (e.g. Booking #BK-CCC5EF70 where total_amount = ₹1198 vs base = ₹899)
        $ctx = $bookingContext;
        if (empty($ctx) && $bookingId > 0) {
            $ctx = Database::fetchOne(
                "SELECT b.*, s.name as service_name, s.starting_price 
                 FROM bookings b 
                 LEFT JOIN services s ON b.service_id = s.id 
                 WHERE b.id = :id LIMIT 1",
                ['id' => $bookingId]
            );
        }

        if (!$ctx) {
            return [];
        }

        $totalAmount = (float)($ctx['total_amount'] ?? 0);
        $basePrice = (float)($ctx['starting_price'] ?? 0);
        $serviceName = !empty($ctx['service_name']) ? $ctx['service_name'] : 'Primary Service';
        $serviceId = (int)($ctx['service_id'] ?? 0);

        if ($totalAmount <= 0) {
            $totalAmount = $basePrice;
        }

        $diff = round($totalAmount - $basePrice, 2);
        if ($diff <= 0 || $basePrice <= 0) {
            return [[
                'service_id'  => $serviceId,
                'name'        => $serviceName,
                'item_type'   => 'service',
                'quantity'    => 1,
                'unit_price'  => $totalAmount,
                'total_price' => $totalAmount,
            ]];
        }

        // Main Service
        $items = [
            [
                'service_id'  => $serviceId,
                'name'        => $serviceName,
                'item_type'   => 'service',
                'quantity'    => 1,
                'unit_price'  => $basePrice,
                'total_price' => $basePrice,
            ]
        ];

        // Attempt exact or closest match from companion add-ons in database
        $matchedAddon = null;
        if ($serviceId > 0) {
            try {
                $availableAddons = ServiceAddon::forService($serviceId);
                foreach ($availableAddons as $cand) {
                    $p = (float)($cand['starting_price'] ?? $cand['base_price'] ?? 0);
                    if (abs($p - $diff) < 1.0) {
                        $matchedAddon = $cand;
                        break;
                    }
                }
            } catch (\Throwable $e) {}
        }

        $addonName = $matchedAddon ? ($matchedAddon['name'] ?? 'Companion Add-on') : 'Selected Service Add-on';
        $addonBadge = $matchedAddon['badge'] ?? '⚡ Add-on';

        $items[] = [
            'service_id'  => $matchedAddon['id'] ?? null,
            'name'        => $addonName,
            'badge'       => $addonBadge,
            'item_type'   => 'addon',
            'quantity'    => 1,
            'unit_price'  => $diff,
            'total_price' => $diff,
        ];

        return $items;
    }
}
