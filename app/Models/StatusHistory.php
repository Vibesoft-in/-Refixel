<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class StatusHistory extends Model
{
    protected static string $table = 'status_history';

    public static function getByJob(int $jobId): array
    {
        return Database::fetchAll(
            "SELECT sh.*, u.name as changed_by_name, u.role as changed_by_role
             FROM status_history sh
             LEFT JOIN users u ON sh.changed_by = u.id
             WHERE sh.job_id = :jid
             ORDER BY sh.created_at ASC",
            ['jid' => $jobId]
        );
    }
}
