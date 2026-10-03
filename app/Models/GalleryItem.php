<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class GalleryItem extends Model
{
    protected static string $table = 'gallery_items';

    public static function getActive(): array
    {
        return Database::fetchAll("SELECT * FROM gallery_items WHERE is_active = 1 ORDER BY sort_order ASC");
    }
}
