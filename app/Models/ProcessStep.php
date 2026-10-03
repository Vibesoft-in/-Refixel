<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ProcessStep extends Model
{
    protected static string $table = 'process_steps';

    public static function getActive(): array
    {
        return Database::fetchAll("SELECT * FROM process_steps WHERE is_active = 1 ORDER BY step_no ASC");
    }
}
