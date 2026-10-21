<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

class SecurityPhase2Task5Test
{
    private PDO $pdo;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function run(): void
    {
        echo "\n=== SecurityPhase2Task5Test: Plain Password Cleanup & Column Drop Verification ===\n";

        $this->testPlainPasswordColumnDroppedFromUsersTable();
        $this->testDropMigrationRollbackSupport();
        $this->testZeroPassword123InSeedAndScripts();
        $this->testNoHardcodedSecretsInGatewaysScript();

        echo "\nResults: {$this->passed} passed, {$this->failed} failed\n";
        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function testPlainPasswordColumnDroppedFromUsersTable(): void
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM users LIKE 'plain_password'");
        $col = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$col) {
            echo "  ✓ PASS: Column 'plain_password' is confirmed DROPPED from users table\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Column 'plain_password' still exists in users table\n";
            $this->failed++;
        }
    }

    private function testDropMigrationRollbackSupport(): void
    {
        $migrationFile = __DIR__ . '/../../database/migrations/2026_10_21_drop_plain_password_column.php';
        if (!file_exists($migrationFile)) {
            echo "  ✗ FAIL: Migration file not found\n";
            $this->failed++;
            return;
        }

        $content = file_get_contents($migrationFile);
        $hasDrop = str_contains($content, 'ALTER TABLE users DROP COLUMN plain_password');
        $hasRollback = str_contains($content, '--rollback') && str_contains($content, 'ALTER TABLE users ADD COLUMN plain_password');

        if ($hasDrop && $hasRollback) {
            echo "  ✓ PASS: Drop migration exists with full --rollback capability\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Drop migration missing DROP or ADD rollback syntax\n";
            $this->failed++;
        }
    }

    private function testZeroPassword123InSeedAndScripts(): void
    {
        $filesToCheck = [
            __DIR__ . '/../../database/seed.sql',
            __DIR__ . '/../../database/add_admin_plain_password_column.php',
            __DIR__ . '/../../database/add_gateways_and_commissions.php',
        ];

        $clean = true;
        foreach ($filesToCheck as $f) {
            if (file_exists($f)) {
                $content = file_get_contents($f);
                if (stripos($content, 'password123') !== false) {
                    echo "  ✗ FAIL: Found 'password123' in " . basename($f) . "\n";
                    $clean = false;
                }
            }
        }

        if ($clean) {
            echo "  ✓ PASS: seed.sql and database setup scripts are 100% free of 'password123'\n";
            $this->passed++;
        } else {
            $this->failed++;
        }
    }

    private function testNoHardcodedSecretsInGatewaysScript(): void
    {
        $gatewaysFile = __DIR__ . '/../../database/add_gateways_and_commissions.php';
        $content = file_get_contents($gatewaysFile);

        $hasLiveKeyHardcoded = str_contains($content, 'rzp_live_');
        $hasSecretHardcoded = str_contains($content, 'ZxuiIKL44pv12Ba5b8rwnwuH');

        if (!$hasLiveKeyHardcoded && !$hasSecretHardcoded) {
            echo "  ✓ PASS: add_gateways_and_commissions.php sanitized; no live credentials hardcoded\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: add_gateways_and_commissions.php still has hardcoded credentials\n";
            $this->failed++;
        }
    }
}

(new SecurityPhase2Task5Test())->run();
