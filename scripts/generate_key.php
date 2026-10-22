<?php
declare(strict_types=1);

/**
 * Script: Generate 32-Byte Base64 Application Key (APP_KEY)
 * 
 * Generates a cryptographic 32-byte key formatted for .env (AES-256-GCM encryption).
 * 
 * Usage:
 *   php scripts/generate_key.php
 */

$key = 'base64:' . base64_encode(random_bytes(32));

echo "========================================================\n";
echo "   REFIXEL APPLICATION KEY GENERATOR (AES-256-GCM)     \n";
echo "========================================================\n\n";

echo "Generated Key:\n";
echo "  " . $key . "\n\n";

echo "To configure on server (e.g., v1.refixel.com):\n";
echo "1. Open your .env file on the server.\n";
echo "2. Add or update the line:\n";
echo "   APP_KEY=" . $key . "\n";
echo "3. Save the file. 2FA configuration can now safely proceed.\n\n";
echo "========================================================\n";
