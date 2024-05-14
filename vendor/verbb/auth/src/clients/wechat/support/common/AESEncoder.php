<?php


namespace verbb\auth\clients\wechat\support\common;

use InvalidArgumentException;

class AESEncoder
{
    /**
     * @param string $text
     * @param string $key
     * @param string $iv
     * @param int $option
     *
     * @return string
     */
    public static function encrypt(string $text, string $key, string $iv, int $option = OPENSSL_RAW_DATA): string
    {
        self::validateKey($key);
        self::validateIv($iv);

        return openssl_encrypt($text, self::getMode($key), $key, $option, $iv);
    }

    /**
     * @param string $cipherText
     * @param string $key
     * @param string $iv
     * @param int $option
     *
     * @return string
     */
    public static function decrypt(string $cipherText, string $key, string $iv, int $option = OPENSSL_RAW_DATA): string
    {
        self::validateKey($key);
        self::validateIv($iv);

        return openssl_decrypt($cipherText, self::getMode($key), $key, $option, $iv);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public static function getMode(string $key): string
    {
        return 'aes-'.(8 * strlen($key)).'-cbc';
    }

    /**
     * @param string $key
     */
    public static function validateKey(string $key): void
    {
        if (!in_array(strlen($key), [16, 24, 32], true)) {
            throw new InvalidArgumentException(
                sprintf('Key length must be 16, 24, or 32 bytes; got key len (%s)
                .', strlen($key))
            );
        }
    }

    /**
     * @param string $iv
     *
     * @throws InvalidArgumentException
     */
    public static function validateIv(string $iv): void
    {
        if (strlen($iv) !== 16) {
            throw new InvalidArgumentException('IV length must be 16 bytes.');
        }
    }
}
