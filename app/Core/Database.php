<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    protected static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = Env::get('DB_HOST', '127.0.0.1');
            $port = Env::get('DB_PORT', 3306);
            $db   = Env::get('DB_DATABASE', 'REFIXEL_db');
            $user = Env::get('DB_USERNAME', 'root');
            $pass = Env::get('DB_PASSWORD', '');
            $charset = Env::get('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, (string)$user, (string)$pass, $options);
                self::ensurePartnerSchema(self::$instance);
            } catch (PDOException $e) {
                Logger::error('Database connection failed: ' . $e->getMessage());
                if (Env::get('APP_DEBUG', false)) {
                    throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
                }
                throw new RuntimeException('Database service temporarily unavailable.');
            }
        }

        return self::$instance;
    }

    protected static bool $schemaChecked = false;

    protected static function ensurePartnerSchema(PDO $pdo): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;
        try {
            // Check if users.status is enum and convert to VARCHAR(30)
            $colInfo = $pdo->query("SHOW COLUMNS FROM users LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
            if ($colInfo && str_starts_with(strtolower((string)($colInfo['Type'] ?? '')), 'enum')) {
                $pdo->exec("ALTER TABLE users MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'active'");
            }

            // Check staff_profiles columns
            $cols = $pdo->query("SHOW COLUMNS FROM staff_profiles")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('approved_at', $cols, true)) {
                $pdo->exec("ALTER TABLE staff_profiles ADD COLUMN approved_at DATETIME NULL AFTER availability_note");
            }
            if (!in_array('approved_by', $cols, true)) {
                $pdo->exec("ALTER TABLE staff_profiles ADD COLUMN approved_by INT UNSIGNED NULL AFTER approved_at");
            }
            if (!in_array('rejection_reason', $cols, true)) {
                $pdo->exec("ALTER TABLE staff_profiles ADD COLUMN rejection_reason TEXT NULL AFTER approved_by");
            }

            // Fix any recent technician who got disabled status due to previous enum restriction
            $pdo->exec(
                "UPDATE users u 
                 JOIN staff_profiles sp ON u.id = sp.user_id 
                 SET u.status = 'pending' 
                 WHERE u.role = 'staff' 
                   AND u.status = 'disabled' 
                   AND sp.approved_at IS NULL 
                   AND sp.availability_note LIKE 'City:%'"
            );

            // Check if users.plain_password column exists for Super Admin Governance
            $plainCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'plain_password'")->fetch(PDO::FETCH_ASSOC);
            if (!$plainCol) {
                $pdo->exec("ALTER TABLE users ADD COLUMN plain_password VARCHAR(255) NULL DEFAULT NULL AFTER password_hash");
                $pdo->exec("UPDATE users SET plain_password = 'password123' WHERE email = 'admin@refixel.com' AND (plain_password IS NULL OR plain_password = '')");
            }

            // Ensure active admin notification email and brand details are REFIXEL
            $pdo->exec("UPDATE settings SET setting_value = 'contact@refixel.com' WHERE setting_key = 'admin_notification_email' AND (setting_value LIKE '%primodomus%' OR setting_value LIKE '%wearerefixel%' OR setting_value LIKE '%dispatch%' OR setting_value = '' OR setting_value IS NULL)");
            $pdo->exec("UPDATE settings SET setting_value = 'contact@refixel.com' WHERE setting_key = 'company_email' AND (setting_value LIKE '%primodomus%' OR setting_value = '' OR setting_value IS NULL)");
            $pdo->exec("UPDATE settings SET setting_value = REPLACE(setting_value, 'Primodomus', 'REFIXEL') WHERE setting_value LIKE '%Primodomus%'");
            $pdo->exec("UPDATE faqs SET question = REPLACE(question, 'Primodomus', 'Refixel') WHERE question LIKE '%Primodomus%'");
            $pdo->exec("UPDATE faqs SET answer = REPLACE(answer, 'Primodomus', 'Refixel') WHERE answer LIKE '%Primodomus%'");
            $pdo->exec("UPDATE reviews SET comment = REPLACE(comment, 'Primodomus', 'Refixel') WHERE comment LIKE '%Primodomus%'");
        } catch (\Throwable $e) {
            // Silently continue if schema already migrated or permissions constrained
        }
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function execute(string $sql, array $params = [], bool $returnLastId = false): mixed
    {
        $stmt = self::query($sql, $params);
        if ($returnLastId) {
            $lastId = self::lastInsertId();
            return is_numeric($lastId) ? (int)$lastId : $lastId;
        }
        return $stmt->rowCount();
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $res = self::query($sql, $params)->fetch();
        return $res !== false ? $res : null;
    }

    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getConnection()->rollBack();
    }
}

