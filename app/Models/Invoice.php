<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Invoice extends Model
{
    protected static string $table = 'invoices';

    public static function findByCustomer(int $customerId): array
    {
        return Database::fetchAll(
            "SELECT i.*, p.amount, p.method, b.id as booking_id, b.booking_no, s.name as service_name
             FROM invoices i
             JOIN payments p ON i.payment_id = p.id
             JOIN bookings b ON p.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE b.customer_id = :cid
             ORDER BY i.issued_at DESC",
            ['cid' => $customerId]
        );
    }

    public static function findWithDetails(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT i.*, p.amount as payment_amount, p.method as payment_method, p.paid_at, p.transaction_ref,
                    b.id as booking_id, b.customer_id, b.booking_no, b.name as customer_name,
                    b.phone as customer_phone, b.email as customer_email,
                    b.address as customer_address, b.pincode,
                    s.name as service_name
             FROM invoices i
             JOIN payments p ON i.payment_id = p.id
             JOIN bookings b ON p.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $id]
        );
    }
}
