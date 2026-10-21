<?php
declare(strict_types=1);

namespace App\Core;

class Encrypter
{
    private const CIPHER = 'aes-256-gcm';

    /**
     * Retrieve the 32-byte encryption key from APP_KEY environment variable.
     * Fails loudly if APP_KEY is missing or invalid.
     *
     * @throws \RuntimeException
     */
    public static function getKey(): string
    {
        $appKey = (string)Env::get('APP_KEY', '');
        if (trim($appKey) === '') {
            throw new \RuntimeException("APP_KEY environment variable is not configured. Sensitive operations (2FA/encryption) cannot proceed.");
        }

        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            if ($decoded === false || strlen($decoded) !== 32) {
                throw new \RuntimeException("APP_KEY is an invalid base64 string or does not decode to 32 bytes.");
            }
            return $decoded;
        }

        if (ctype_xdigit($appKey) && strlen($appKey) === 64) {
            return (string)hex2bin($appKey);
        }

        if (strlen($appKey) >= 32) {
            return substr(hash('sha256', $appKey, true), 0, 32);
        }

        throw new \RuntimeException("APP_KEY is too short (must be at least 32 characters or base64:32bytes).");
    }

    /**
     * Check if a valid APP_KEY is configured.
     */
    public static function hasKey(): bool
    {
        try {
            self::getKey();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Encrypt a string using AES-256-GCM.
     *
     * @throws \RuntimeException
     */
    public static function encrypt(string $value): string
    {
        $key = self::getKey();
        $iv = random_bytes(12); // Standard 96-bit IV for GCM
        $tag = '';

        $ciphertext = openssl_encrypt(
            $value,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($ciphertext === false) {
            throw new \RuntimeException("Encryption failed: " . (openssl_error_string() ?: 'unknown error'));
        }

        $payload = [
            'iv'    => base64_encode($iv),
            'value' => base64_encode($ciphertext),
            'tag'   => base64_encode($tag),
        ];

        return base64_encode((string)json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * Decrypt an encrypted string using AES-256-GCM.
     * Returns null if key is invalid, payload is malformed, or authentication tag fails.
     */
    public static function decrypt(string $payload): ?string
    {
        try {
            $key = self::getKey();
        } catch (\Throwable) {
            return null;
        }

        $json = base64_decode($payload, true);
        if ($json === false) {
            return null;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        if (!is_array($data) || empty($data['iv']) || empty($data['value']) || empty($data['tag'])) {
            return null;
        }

        $iv = base64_decode((string)$data['iv'], true);
        $ciphertext = base64_decode((string)$data['value'], true);
        $tag = base64_decode((string)$data['tag'], true);

        if ($iv === false || $ciphertext === false || $tag === false || strlen($tag) !== 16) {
            return null;
        }

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $decrypted !== false ? $decrypted : null;
    }
}
