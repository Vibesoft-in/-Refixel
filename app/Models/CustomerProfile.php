<?php
declare(strict_types=1);

namespace App\Models;

class CustomerProfile extends Model
{
    protected static string $table = 'customer_profiles';
    protected static string $primaryKey = 'user_id';
}
