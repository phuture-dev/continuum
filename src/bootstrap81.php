<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Phuture\Continuum\Php81;

if (\PHP_VERSION_ID >= 80100) {
    return;
}

/**
 * PHP 8.1 constants
 */
if (extension_loaded('gd')) {
    if (!defined('IMAGETYPE_AVIF')) {
        define('IMAGETYPE_AVIF', Php81::IMAGETYPE_AVIF);
    }

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
