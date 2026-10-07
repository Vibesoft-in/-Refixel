<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BookingAttachment extends Model
{
    protected static string $table = 'booking_attachments';

    public static function getByBooking(int $bookingId): array
    {
        return Database::fetchAll("SELECT * FROM booking_attachments WHERE booking_id = :bid", ['bid' => $bookingId]);
    }
}
