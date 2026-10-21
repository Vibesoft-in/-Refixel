<?php
declare(strict_types=1);

namespace App\Core;

/**
 * RFC 6238 TOTP (Time-based One-Time Password) & Recovery Codes Engine
 */
class Totp
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const PERIOD = 30; // 30 seconds
    private const DIGITS = 6;

    /**
     * Generate a cryptographically secure Base32 secret for TOTP.
     * Default length 16 chars (80 bits of entropy).
     */
    public static function generateSecret(int $length = 16): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $alphabetLength = strlen($alphabet);
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, $alphabetLength - 1)];
        }

        return $secret;
    }

    /**
     * Encode binary data into RFC 4648 Base32 string.
     */
    public static function base32Encode(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $alphabet = self::BASE32_ALPHABET;
        $binaryString = '';
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $binaryString .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($binaryString, 5);
        $output = '';

        foreach ($chunks as $chunk) {
            $output .= $alphabet[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $output;
    }

    /**
     * Decode an RFC 4648 Base32 string into binary bytes.
     */
    public static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(trim($b32));
        $b32 = rtrim($b32, '=');
        if ($b32 === '') {
            return '';
        }

        $alphabet = self::BASE32_ALPHABET;
        $binaryString = '';
        $length = strlen($b32);

        for ($i = 0; $i < $length; $i++) {
            $char = $b32[$i];
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue; // Skip invalid characters
            }
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = str_split($binaryString, 8);
        $output = '';

        foreach ($bytes as $byte) {
            if (strlen($byte) === 8) {
                $output .= chr((int)bindec($byte));
            }
        }

        return $output;
    }

    /**
     * Generate 6-digit TOTP code for given secret and time-step.
     * Implements RFC 6238 / RFC 4226 HMAC-SHA1 algorithm.
     */
    public static function generateCode(string $secret, ?int $timeStep = null): string
    {
        if ($timeStep === null) {
            $timeStep = (int)floor(time() / self::PERIOD);
        }

        $secretBytes = self::base32Decode($secret);

        // 8-byte big-endian counter
        $packedTime = pack('N*', 0) . pack('N*', $timeStep);

        // HMAC-SHA1
        $hash = hash_hmac('sha1', $packedTime, $secretBytes, true);

        // Dynamic truncation
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % (10 ** self::DIGITS);

        return str_pad((string)$otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit TOTP code with time-step drift window and REPLAY REJECTION.
     * 
     * @param string $secret Plain Base32 secret
     * @param string $code User-provided 6-digit code
     * @param int|null $lastStep Last used time-step stored in DB (for replay protection)
     * @param int $discrepancy Allowed drift in 30s steps (default: 1 step = ±30s)
     * @return int|null Matched timeStep on success, or null on failure/replay
     */
    public static function verify(string $secret, string $code, ?int $lastStep = null, int $discrepancy = 1): ?int
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== self::DIGITS) {
            return null;
        }

        $currentStep = (int)floor(time() / self::PERIOD);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $step = $currentStep + $i;
            $expectedCode = self::generateCode($secret, $step);

            if (hash_equals($expectedCode, $code)) {
                // REPLAY PROTECTION:
                // If this code corresponds to a time-step that was already used, reject it!
                if ($lastStep !== null && $step <= $lastStep) {
                    Logger::warning("TOTP Replay attack detected: step {$step} is <= last recorded step {$lastStep}");
                    return null;
                }

                return $step;
            }
        }

        return null;
    }

    /**
     * Generate 8 single-use recovery codes (e.g. "A1B2-C3D4").
     * Returns both plain (for display once to user) and SHA-256 hashed (for database storage).
     *
     * @return array{plain: string[], hashed: string[]}
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $plain = [];
        $hashed = [];

        for ($i = 0; $i < $count; $i++) {
            $part1 = strtoupper(bin2hex(random_bytes(2)));
            $part2 = strtoupper(bin2hex(random_bytes(2)));
            $code = "{$part1}-{$part2}";

            $plain[] = $code;
            $hashed[] = hash('sha256', self::normalizeCode($code));
        }

        return [
            'plain'  => $plain,
            'hashed' => $hashed,
        ];
    }

    /**
     * Normalize a recovery code or OTP code (uppercase, strip non-alphanumeric).
     */
    public static function normalizeCode(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
    }

    /**
     * Verify a recovery code against stored hashes.
     * Returns the array index of the matched code (to allow deletion), or null if invalid.
     *
     * @param string $inputCode The recovery code submitted by the user
     * @param string[] $hashedCodes Array of SHA-256 hashes from DB
     * @return int|null
     */
    public static function verifyRecoveryCode(string $inputCode, array $hashedCodes): ?int
    {
        $normalized = self::normalizeCode($inputCode);
        if (strlen($normalized) < 8) {
            return null;
        }

        $inputHash = hash('sha256', $normalized);

        foreach ($hashedCodes as $index => $storedHash) {
            if (hash_equals($storedHash, $inputHash)) {
                return (int)$index;
            }
        }

        return null;
    }

    /**
     * Generate standard otpauth:// URI for QR code generation.
     */
    public static function getOtpAuthUri(string $company, string $accountIdentifier, string $secret): string
    {
        $label = rawurlencode($company) . ':' . rawurlencode($accountIdentifier);
        $issuer = rawurlencode($company);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }
}
