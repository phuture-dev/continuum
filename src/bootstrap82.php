<?php

declare(strict_types=1);

use Phuture\Continuum\Php82;

if (\PHP_VERSION_ID >= 80200) {
    return;
}

/**
 * PHP 8.2 functions
 */
if (extension_loaded('mysqli')) {
    if (!function_exists('mysqli_execute_query')) {
        /**
         * Executes a prepared statement with parameters.
         *
         * @param mysqli $mysqli The MySQL connection
         * @param string $query The SQL query with placeholders
         * @param array|null $params Parameters to bind to the query
         * @return mysqli_result|bool Returns a result set for SELECT queries, or true/false for other queries
         */
        function mysqli_execute_query(
            mysqli $mysqli,
            string $query,
            ?array $params = null
        ): mysqli_result|bool {
            return Php82::mysqli_execute_query($mysqli, $query, $params);
        }
    }
}

if (extension_loaded('openssl')) {
    if (!function_exists('openssl_cipher_key_length')) {
        /**
         * Gets the required key length for an OpenSSL cipher.
         *
         * @param string $cipherAlgorithm The OpenSSL cipher algorithm name
         * @return int|false Returns the key length in bytes, or false if the cipher is unknown
         */
        function openssl_cipher_key_length(string $cipherAlgorithm): int|false
        {
            return Php82::openssl_cipher_key_length($cipherAlgorithm);
        }
    }
}

if (!function_exists('memory_reset_peak_usage')) {
    /**
     * Resets the peak memory usage.
     *
     * @return void
     */
    function memory_reset_peak_usage(): void
    {
        Php82::memory_reset_peak_usage();
    }
}

if (extension_loaded('curl')) {
    if (!function_exists('curl_upkeep')) {
        /**
         * Performs maintenance on a cURL handle to keep connections alive.
         *
         * @param CurlHandle $handle The cURL handle to perform maintenance on
         * @return bool Returns true on success
         */
        function curl_upkeep(CurlHandle $handle): bool
        {
            return Php82::curl_upkeep($handle);
        }
    }
}

if (extension_loaded('sodium')) {
    if (!function_exists('sodium_crypto_stream_xchacha20_xor_ic')) {
        /**
         * Encrypts data using the XChaCha20 stream cipher.
         *
         * @param string $message The data to encrypt
         * @param string $nonce The 24-byte nonce
         * @param int $counter The initialization counter
         * @param string $key The 32-byte key
         * @return string The encrypted data
         */
        function sodium_crypto_stream_xchacha20_xor_ic(
            string $message,
            string $nonce,
            int $counter,
            string $key
        ): string {
            return Php82::sodium_crypto_stream_xchacha20_xor_ic(
                $message,
                $nonce,
                $counter,
                $key
            );
        }
    }
}

if (extension_loaded('libxml')) {
    if (!function_exists('libxml_get_external_entity_loader')) {
        /**
         * Gets the current external entity loader.
         *
         * @return callable|null The current external entity loader callback, or null if none is set
         */
        function libxml_get_external_entity_loader(): ?callable
        {
            return Php82::libxml_get_external_entity_loader();
        }
    }
}

if (extension_loaded('imap')) {
    if (!function_exists('imap_is_open')) {
        /**
         * Checks whether an IMAP connection is still open.
         *
         * @param mixed $imapStream The IMAP stream to check
         * @return bool Returns true if the stream is open, false otherwise
         */
        function imap_is_open(mixed $imapStream): bool
        {
            return Php82::imap_is_open($imapStream);
        }
    }
}
