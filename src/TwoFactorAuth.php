<?php

/**
 * Prosta implementacja TOTP (Time-based One-Time Password) zgodna z RFC 6238.
 * Bez zewnętrznych zależności. Wymaga PHP 8.1+.
 */
class TwoFactorAuth
{
    private const PERIOD = 30;
    private const DIGITS = 6;
    private const ALGORITHM = 'sha1';
    private const SECRET_LENGTH = 20; // 160 bits
    private const BACKUP_CODES_COUNT = 10;
    private const WINDOW = 1; // Accept codes ±1 period

    private static string $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random base32-encoded secret.
     */
    public static function generateSecret(): string
    {
        $bytes = random_bytes(self::SECRET_LENGTH);
        return self::base32Encode($bytes);
    }

    /**
     * Generate a TOTP code for the given secret and time.
     */
    public static function getCode(string $secret, ?int $time = null): string
    {
        $time ??= time();
        $timeSlice = intdiv($time, self::PERIOD);

        $secretBytes = self::base32Decode($secret);

        // Pack time as 8-byte big-endian
        $timeBytes = pack('N*', 0, $timeSlice);

        $hash = hash_hmac(self::ALGORITHM, $timeBytes, $secretBytes, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string)$code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a TOTP code with time window tolerance.
     */
    public static function verifyCode(string $secret, string $code, ?int $time = null): bool
    {
        $time ??= time();
        $code = trim($code);

        if (strlen($code) !== self::DIGITS || !ctype_digit($code)) {
            return false;
        }

        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            $checkTime = $time + ($i * self::PERIOD);
            if (hash_equals(self::getCode($secret, $checkTime), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a set of one-time backup codes.
     */
    public static function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::BACKUP_CODES_COUNT; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))); // 8-char hex codes
        }
        return $codes;
    }

    /**
     * Generate otpauth:// URI for QR code generation.
     */
    public static function getOtpAuthUri(string $secret, string $label, string $issuer = 'RedirectCMS'): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $label)
            . '?secret=' . $secret
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=' . strtoupper(self::ALGORITHM)
            . '&digits=' . self::DIGITS
            . '&period=' . self::PERIOD;
    }

    /**
     * Base32 encode binary data.
     */
    private static function base32Encode(string $data): string
    {
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $result = '';
        $chunks = str_split($binary, 5);
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= self::$base32Chars[bindec($chunk)];
        }

        return $result;
    }

    /**
     * Base32 decode to binary data.
     */
    private static function base32Decode(string $data): string
    {
        $data = strtoupper($data);
        $binary = '';

        for ($i = 0; $i < strlen($data); $i++) {
            $pos = strpos(self::$base32Chars, $data[$i]);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $result = '';
        $chunks = str_split($binary, 8);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 8) break;
            $result .= chr(bindec($chunk));
        }

        return $result;
    }
}
