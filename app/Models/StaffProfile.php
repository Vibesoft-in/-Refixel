<?php
declare(strict_types=1);

namespace App\Models;

class StaffProfile extends Model
{
    protected static string $table = 'staff_profiles';
    protected static string $primaryKey = 'user_id';

    public static function find(int $userId): ?array
    {
        return \App\Core\Database::fetchOne(
            "SELECT sp.*, u.name, u.email, u.phone, u.status 
             FROM staff_profiles sp
             JOIN users u ON sp.user_id = u.id
             WHERE sp.user_id = :uid",
            ['uid' => $userId]
        );
    }

    public static function getSkills(int $userId): array
    {
        return \App\Core\Database::fetchAll(
            "SELECT c.id, c.name, c.slug, c.icon 
             FROM staff_skills ss
             JOIN categories c ON ss.category_id = c.id
             WHERE ss.staff_id = :uid
             ORDER BY c.name ASC",
            ['uid' => $userId]
        );
    }

    public static function updateAvailability(int $userId, bool $isAvailable, ?string $note = null): void
    {
        $existing = \App\Core\Database::fetchOne(
            "SELECT user_id FROM staff_profiles WHERE user_id = :uid",
            ['uid' => $userId]
        );

        if ($existing) {
            \App\Core\Database::query(
                "UPDATE staff_profiles SET is_available = :avail, availability_note = :note WHERE user_id = :uid",
                [
                    'avail' => $isAvailable ? 1 : 0,
                    'note'  => $note,
                    'uid'   => $userId,
                ]
            );
        } else {
            \App\Core\Database::query(
                "INSERT INTO staff_profiles (user_id, is_available, availability_note, rating_avg, rating_count, created_at)
                 VALUES (:uid, :avail, :note, 5.00, 0, NOW())",
                [
                    'uid'   => $userId,
                    'avail' => $isAvailable ? 1 : 0,
                    'note'  => $note,
                ]
            );
        }
    }
}
