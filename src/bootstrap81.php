<?php

declare(strict_types=1);

use Phuture\Continuum\Php81;

if (\PHP_VERSION_ID >= 80100) {
    return;
}

/**
 * PHP 8.1 constants
 */
if (extension_loaded('gd')) {
    if (!defined('IMG_AVIF')) {
        define('IMG_AVIF', Php81::IMG_AVIF);
    }

    if (!defined('IMG_WEBP_LOSSLESS')) {
        define('IMG_WEBP_LOSSLESS', Php81::IMG_WEBP_LOSSLESS);
    }
}

if (extension_loaded('mysqli')) {
    if (!defined('MYSQLI_REFRESH_REPLICA')) {
        define('MYSQLI_REFRESH_REPLICA', Php81::MYSQLI_REFRESH_REPLICA);
    }
}

if (extension_loaded('tokenizer')) {
    if (!defined('T_READONLY')) {
        define('T_READONLY', Php81::T_READONLY);
    }
}

/**
 * PHP 8.1 functions
 */
if (!function_exists('fsync')) {
    /**
     * Synchronizes changes to a file including metadata.
     *
     * @param mixed $stream The file stream to sync
     * @return bool Returns true on success
     */
    function fsync(mixed $stream): bool
    {
        return Php81::fsync($stream);
    }
}

if (!function_exists('fdatasync')) {
    /**
     * Synchronizes data to a file without syncing metadata.
     *
     * @param mixed $stream The file stream to sync
     * @return bool Returns true on success
     */
    function fdatasync(mixed $stream): bool
    {
        return Php81::fdatasync($stream);
    }
}

if (extension_loaded('gd')) {
    if (!function_exists('imagecreatefromavif')) {
        /**
         * Creates a new image from an AVIF file.
         *
         * @param string $filename Path to the AVIF image file
         * @return mixed Returns an image object on success, false on failure
         */
        function imagecreatefromavif(string $filename): mixed
        {
            return Php81::imagecreatefromavif($filename);
        }
    }

    if (!function_exists('imageavif')) {
        /**
         * Outputs an image to an AVIF file or browser.
         *
         * @param mixed $image The image resource
         * @param mixed $file The output file path or null to output directly
         * @param int $quality The compression quality (0-100), -1 for default
         * @param int $speed The encoding speed (0-10), -1 for default
         * @return bool Returns true on success
         */
        function imageavif(mixed $image, mixed $file = null, int $quality = -1, int $speed = -1): bool
        {
            return Php81::imageavif($image, $file, $quality, $speed);
        }
    }
}

if (extension_loaded('brotli')) {
    if (!function_exists('brotli_compress')) {
        /**
         * Compresses data using the Brotli algorithm.
         *
         * @param string $data The data to compress
         * @param int $encoding The encoding mode (0=generic, 1=text, 2=font)
         * @param int $quality The compression quality (0-11)
         * @return mixed Returns the compressed data, or false on failure
         */
        function brotli_compress(string $data, int $encoding = 0, int $quality = 11): mixed
        {
            return Php81::brotli_compress($data, $encoding, $quality);
        }
    }

    if (!function_exists('brotli_decompress')) {
        /**
         * Decompresses Brotli-encoded data.
         *
         * @param string $data The compressed data
         * @param int $max_length Maximum length of decompressed data (0 for unlimited)
         * @return mixed Returns the decompressed data, or false on failure
         */
        function brotli_decompress(string $data, int $max_length = 0): mixed
        {
            return Php81::brotli_decompress($data, $max_length);
        }
    }
}
