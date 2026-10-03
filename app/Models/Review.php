<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Review extends Model
{
    protected static string $table = 'reviews';

    public static function getApproved(): array
    {
        return Database::fetchAll(
            "SELECT r.*, u.name as customer_name, s.name as service_name
             FROM reviews r
             JOIN users u ON r.customer_id = u.id
             JOIN bookings b ON r.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE r.is_approved = 1
             ORDER BY r.created_at DESC"
        );
    }

    public static function getByService(int $serviceId, int $limit = 5): array
    {
        $reviews = Database::fetchAll(
            "SELECT r.*, u.name as customer_name, s.name as service_name
             FROM reviews r
             JOIN users u ON r.customer_id = u.id
             JOIN bookings b ON r.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE r.is_approved = 1 AND b.service_id = :sid
             ORDER BY r.created_at DESC LIMIT {$limit}",
            ['sid' => $serviceId]
        );

        if (!empty($reviews)) {
            return $reviews;
        }

        // Fallback to approved reviews so customer always sees authentic proof
        return Database::fetchAll(
            "SELECT r.*, u.name as customer_name, s.name as service_name
             FROM reviews r
             JOIN users u ON r.customer_id = u.id
             JOIN bookings b ON r.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE r.is_approved = 1
             ORDER BY r.created_at DESC LIMIT {$limit}"
        );
    }

    public static function findByBooking(int $bookingId): ?array
    {
        return Database::fetchOne(
            "SELECT r.*, u.name as customer_name
             FROM reviews r
             JOIN users u ON r.customer_id = u.id
             WHERE r.booking_id = :bid
             LIMIT 1",
            ['bid' => $bookingId]
        );
    }

    public static function hasCustomerReviewed(int $bookingId): bool
    {
        $res = Database::fetchOne(
            "SELECT id FROM reviews WHERE booking_id = :bid LIMIT 1",
            ['bid' => $bookingId]
        );
        return !empty($res);
    }
}

