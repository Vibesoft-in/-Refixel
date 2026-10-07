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
        return Database::fetchOne(
            "SELECT b.*, s.name as service_name, s.starting_price, c.name as category_name,
                    u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                    j.id as job_id, j.status as job_status, j.staff_id, st.name as staff_name
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
    }
}
