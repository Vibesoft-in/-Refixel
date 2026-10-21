<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

class SecurityPhase2Task1Test
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
        echo "\n=== SecurityPhase2Task1Test: Payments & Bookings Schema Verification ===\n";

        $this->testPaymentsTableColumns();
        $this->testPaymentsTableIndexes();
        $this->testBookingsTableColumnsAndIndex();
        $this->testNoCardOrCvvColumns();

        echo "\nResults: {$this->passed} passed, {$this->failed} failed\n";
        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function testPaymentsTableColumns(): void
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM payments");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $hasOrderId = in_array('gateway_order_id', $cols, true);
        $hasPaymentId = in_array('gateway_payment_id', $cols, true);

        if ($hasOrderId && $hasPaymentId) {
            echo "  ✓ PASS: payments table has gateway_order_id and gateway_payment_id columns\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: payments table missing required gateway columns\n";
            $this->failed++;
        }
    }

    private function testPaymentsTableIndexes(): void
    {
        $stmt = $this->pdo->prepare("SHOW INDEX FROM payments WHERE Key_name IN ('idx_payments_gateway_order_id', 'uq_payments_gateway_payment_id')");
        $stmt->execute();
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hasOrderIdx = false;
        $hasPaymentUnique = false;

        foreach ($indexes as $idx) {
            if ($idx['Key_name'] === 'idx_payments_gateway_order_id') {
                $hasOrderIdx = true;
            }
            if ($idx['Key_name'] === 'uq_payments_gateway_payment_id' && (int)$idx['Non_unique'] === 0) {
                $hasPaymentUnique = true;
            }
        }

        if ($hasOrderIdx && $hasPaymentUnique) {
            echo "  ✓ PASS: payments table has indexed gateway_order_id and UNIQUE gateway_payment_id\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: payments table missing expected indexes\n";
            $this->failed++;
        }
    }

    private function testBookingsTableColumnsAndIndex(): void
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM bookings");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $hasOrderId = in_array('gateway_order_id', $cols, true);

        $stmt2 = $this->pdo->prepare("SHOW INDEX FROM bookings WHERE Key_name = 'uq_bookings_gateway_order_id'");
        $stmt2->execute();
        $idx = $stmt2->fetch(PDO::FETCH_ASSOC);
        $isUnique = $idx && (int)$idx['Non_unique'] === 0;

        if ($hasOrderId && $isUnique) {
            echo "  ✓ PASS: bookings table has gateway_order_id with UNIQUE index\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: bookings table missing gateway_order_id or UNIQUE index\n";
            $this->failed++;
        }
    }

    private function testNoCardOrCvvColumns(): void
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM payments");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $sensitive = ['cvv', 'cvc', 'card_number', 'card_cvv', 'card_expiry', 'upi_pin', 'pin'];
        $leaked = array_intersect($sensitive, array_map('strtolower', $cols));

        if (empty($leaked)) {
            echo "  ✓ PASS: No PCI/cardholder/sensitive auth data stored in payments table\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Sensitive columns found in payments: " . implode(', ', $leaked) . "\n";
            $this->failed++;
        }
    }
}

(new SecurityPhase2Task1Test())->run();
