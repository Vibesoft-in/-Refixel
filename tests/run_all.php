<?php
declare(strict_types=1);

$testFiles = array_merge(
    glob(__DIR__ . '/Feature/*Test.php') ?: [],
    glob(__DIR__ . '/Unit/*Test.php') ?: []
);

$passed = 0;
$failed = 0;
$failures = [];

echo "Running " . count($testFiles) . " test suites...\n\n";

foreach ($testFiles as $file) {
    $name = basename($file);
    $cmd = 'php ' . escapeshellarg($file);
    exec($cmd, $output, $code);
    if ($code === 0) {
        $passed++;
        echo "  [PASS] {$name}\n";
    } else {
        $failed++;
        $failures[] = [
            'file'   => $name,
            'output' => implode("\n", array_slice($output, -10)),
        ];
        echo "  [FAIL] {$name}\n";
    }
}

echo "\n=========================================\n";
echo "Summary: {$passed} Passed, {$failed} Failed (Total: " . count($testFiles) . ")\n";
echo "=========================================\n";

if ($failed > 0) {
    echo "\nFailures:\n";
    foreach ($failures as $f) {
        echo "--- {$f['file']} ---\n{$f['output']}\n\n";
    }
    exit(1);
}

exit(0);
