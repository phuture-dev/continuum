<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use mysqli;
use CurlHandle;
use mysqli_result;
use RuntimeException;
use mysqli_sql_exception;

/**
 * PHP 8.2 polyfill methods.
 *
 * This class provides static methods to polyfill PHP 8.2 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php82
{
    /**
     * Lookup table for OpenSSL cipher key lengths.
     *
     * Maps cipher algorithm names to their required key lengths in bytes.
     * This covers the most commonly used OpenSSL ciphers.
     *
     * @var array
     */
    private static array $cipherKeyLengths = [
        // AES ciphers
        'AES-128-CBC' => 16,
        'AES-128-CFB' => 16,
        'AES-128-CFB1' => 16,
        'AES-128-CFB8' => 16,
        'AES-128-CTR' => 16,
        'AES-128-ECB' => 16,
        'AES-128-OFB' => 16,
        'AES-128-XTS' => 16,
        'AES-192-CBC' => 24,
        'AES-192-CFB' => 24,
        'AES-192-CFB1' => 24,
        'AES-192-CFB8' => 24,
        'AES-192-CTR' => 24,
        'AES-192-ECB' => 24,
        'AES-192-OFB' => 24,
        'AES-256-CBC' => 32,
        'AES-256-CFB' => 32,
        'AES-256-CFB1' => 32,
        'AES-256-CFB8' => 32,
        'AES-256-CTR' => 32,
        'AES-256-ECB' => 32,
        'AES-256-OFB' => 32,
        'AES-256-XTS' => 32,
        'AES-128-GCM' => 16,
        'AES-192-GCM' => 24,
        'AES-256-GCM' => 32,
        // DES ciphers
        'DES-CBC' => 8,
        'DES-CFB' => 8,
        'DES-CFB1' => 8,
        'DES-CFB8' => 8,
        'DES-ECB' => 8,
        'DES-OFB' => 8,
        'DES-EDE' => 16,
        'DES-EDE-CBC' => 16,
        'DES-EDE-CFB' => 16,
        'DES-EDE-OFB' => 16,
        'DES-EDE3' => 24,
        'DES-EDE3-CBC' => 24,
        'DES-EDE3-CFB' => 24,
        'DES-EDE3-OFB' => 24,
        'DES3' => 24,
        // Camellia ciphers
        'CAMELLIA-128-CBC' => 16,
        'CAMELLIA-128-CFB' => 16,
        'CAMELLIA-128-ECB' => 16,
        'CAMELLIA-128-OFB' => 16,
        'CAMELLIA-192-CBC' => 24,
        'CAMELLIA-192-CFB' => 24,
        'CAMELLIA-192-ECB' => 24,
        'CAMELLIA-192-OFB' => 24,
        'CAMELLIA-256-CBC' => 32,
        'CAMELLIA-256-CFB' => 32,
        'CAMELLIA-256-ECB' => 32,
        'CAMELLIA-256-OFB' => 32,
        // RC ciphers
        'RC2-CBC' => 16,
        'RC2-CFB' => 16,
        'RC2-ECB' => 16,
        'RC2-OFB' => 16,
        'RC4' => 16,
        'RC4-40' => 5,
        'RC4-HMAC-MD5' => 16,
        // Blowfish
        'BF-CBC' => 16,
        'BF-CFB' => 16,
        'BF-ECB' => 16,
        'BF-OFB' => 16,
        // ChaCha ciphers
        'CHACHA20' => 32,
        'CHACHA20-POLY1305' => 32,
        // CAST
        'CAST5-CBC' => 16,
        'CAST5-CFB' => 16,
        'CAST5-ECB' => 16,
        'CAST5-OFB' => 16,
        // IDEA
        'IDEA-CBC' => 16,
        'IDEA-CFB' => 16,
        'IDEA-ECB' => 16,
        'IDEA-OFB' => 16,
        // SEED
        'SEED-CBC' => 16,
        'SEED-CFB' => 16,
        'SEED-ECB' => 16,
        'SEED-OFB' => 16
    ];

    /**
     * Performs maintenance on a cURL handle.
     *
     * This is a best-effort polyfill for the curl_upkeep() function introduced in PHP 8.2.
     * The native function performs connection maintenance on HTTP/2 connections to keep
     * them alive. Since this cannot be fully replicated in userland PHP, this method
     * attempts to configure the cURL handle to encourage connection reuse and longevity.
     *
     * Note: This is not a 100% accurate polyfill. For full functionality, use PHP 8.2+
     * with libcurl 7.62.0 or later.
     *
     * @see https://www.php.net/manual/en/function.curl-upkeep.php
     *
     * @param CurlHandle $handle The cURL handle to perform maintenance on
     * @return bool Returns true on success
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function curl_upkeep(CurlHandle $handle): bool
    {
        curl_setopt($handle, CURLOPT_FORBID_REUSE, false);
        curl_setopt($handle, CURLOPT_FRESH_CONNECT, false);

        return true;
    }

    /**
     * Checks whether an IMAP connection is still open.
     *
     * This method provides a polyfill for the imap_is_open() function introduced in PHP 8.2.1.
     * It checks if the given IMAP stream resource is still valid and open.
     *
     * @see https://www.php.net/manual/en/function.imap-is-open.php
     *
     * @param resource|object $imapStream The IMAP stream to check
     * @return bool Returns true if the stream is open, false otherwise
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function imap_is_open($imapStream): bool
    {
        // Handle PHP 8.1+ IMAP\Connection object
        if (is_object($imapStream)) {
            // Check if it's an IMAP\Connection instance
            return $imapStream instanceof \IMAP\Connection;
        }

        // Handle legacy resource type
        if (!is_resource($imapStream)) {
            return false;
        }

        // Check if it's a valid IMAP resource type
        $resourceType = get_resource_type($imapStream);
        if ($resourceType === 'Unknown') {
            // In PHP 8.1+, some resources may show as 'Unknown'
            // Try to validate by attempting a safe operation
            $errors = [];
            set_error_handler(function (int $errno, string $errstr) use (&$errors): bool {
                $errors[] = $errstr;

                return true;
            });

            // Try to check mailbox status - a harmless operation that will fail if closed
            @imap_check($imapStream);
            restore_error_handler();

            return empty($errors);
        }

        return $resourceType === 'imap';
    }

    /**
     * Resets the peak memory usage.
     *
     * This method provides a no-op implementation for PHP < 8.2. In PHP 8.2+,
     * this function resets the peak memory usage tracked by memory_get_peak_usage().
     * Since the internal counter cannot be reset in userland PHP, this method
     * does nothing but provides API compatibility.
     *
     * @see https://www.php.net/manual/en/function.memory-reset-peak-usage.php
     *
     * @return void
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function memory_reset_peak_usage(): void
    {
        // No-op implementation for PHP < 8.2
        // The internal peak memory counter cannot be reset from userland PHP.
        // This method exists solely for API compatibility.
    }

    /**
     * Executes a prepared statement with parameters.
     *
     * This method prepares a SQL query, binds the given parameters, and executes it.
     * It combines three operations into one convenient method call.
     *
     * @see https://www.php.net/manual/en/mysqli.execute-query.php
     *
     * @param mysqli $mysqli The MySQL connection
     * @param string $query The SQL query with placeholders (use '?' for parameters)
     * @param array|null $params Parameters to bind to the query (default: null)
     * @return mysqli_result|bool Returns a result set for SELECT queries, or true/false for other queries
     * @throws mysqli_sql_exception If the query preparation or execution fails
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mysqli_execute_query(
        mysqli $mysqli,
        string $query,
        ?array $params = null
    ): mysqli_result|bool {
        $stmt = mysqli_prepare($mysqli, $query);

        if ($stmt === false) {
            throw new mysqli_sql_exception(mysqli_error($mysqli), mysqli_errno($mysqli));
        }

        if ($params !== null && count($params) > 0) {
            $types = self::buildMysqliBindTypes($params);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        $result = mysqli_stmt_execute($stmt);

        if ($result === false) {
            throw new mysqli_sql_exception(mysqli_stmt_error($stmt), mysqli_stmt_errno($stmt));
        }

        $resultSet = mysqli_stmt_get_result($stmt);

        if ($resultSet !== false) {
            mysqli_stmt_close($stmt);

            return $resultSet;
        }

        mysqli_stmt_close($stmt);

        return $result;
    }

    /**
     * Gets the required key length for an OpenSSL cipher.
     *
     * This method returns the number of bytes required for the key of a given
     * cipher algorithm. This is useful when generating encryption keys.
     *
     * @see https://www.php.net/manual/en/function.openssl-cipher-key-length.php
     *
     * @param string $cipherAlgorithm The OpenSSL cipher algorithm name (e.g., 'AES-128-CBC')
     * @return int|false Returns the key length in bytes, or false if the cipher is unknown
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function openssl_cipher_key_length(string $cipherAlgorithm): int|false
    {
        $upperCipher = strtoupper($cipherAlgorithm);

        if (isset(self::$cipherKeyLengths[$upperCipher])) {
            return self::$cipherKeyLengths[$upperCipher];
        }

        // Try to get cipher methods dynamically
        if (function_exists('openssl_get_cipher_methods')) {
            $methods = openssl_get_cipher_methods();

            foreach ($methods as $method) {
                if (strtoupper($method) === $upperCipher) {
                    // Extract key length from the IV length for common ciphers
                    $ivLength = @openssl_cipher_iv_length($method);

                    // For AES-GCM, we need to infer from the name
                    if (str_contains($upperCipher, '-128-')) {
                        return 16;
                    }
                    if (str_contains($upperCipher, '-192-')) {
                        return 24;
                    }
                    if (str_contains($upperCipher, '-256-')) {
                        return 32;
                    }

                    // For DES-based ciphers
                    if (str_contains($upperCipher, 'DES-EDE3')) {
                        return 24;
                    }
                    if (str_contains($upperCipher, 'DES-EDE')) {
                        return 16;
                    }
                    if (str_contains($upperCipher, 'DES-')) {
                        return 8;
                    }

                    // Default to 16 if we can't determine
                    return 16;
                }
            }
        }

        return false;
    }

    /**
     * Builds the type string for mysqli_stmt_bind_param.
     *
     * This helper method determines the type string for binding parameters
     * to a prepared statement based on the types of values in the array.
     *
     * @param array $params The parameters to bind
     * @return string The type string for mysqli_stmt_bind_param
     */
    private static function buildMysqliBindTypes(array $params): string
    {
        $types = '';

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_null($param)) {
                $types .= 's';
            } elseif (is_bool($param)) {
                $types .= 'i';
            } else {
                $types .= 's';
            }
        }

        return $types;
    }
}
