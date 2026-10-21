<?php
declare(strict_types=1);

/**
 * DEPRECATED & NEUTRALIZED (Security Phase 2)
 * 
 * The plain_password column has been permanently dropped from users table.
 * All authentication operates exclusively on bcrypt password_hash.
 */

require_once __DIR__ . '/../app/Core/Env.php';
require_once __DIR__ . '/../app/Core/Database.php';

echo "Notice: plain_password column was permanently removed in Security Phase 2. This script is decommissioned.\n";
exit(0);
