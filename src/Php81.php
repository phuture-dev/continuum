<?php

declare(strict_types=1);

namespace Phuture\Continuum;

/**
 * PHP 8.1 polyfill methods.
 *
 * This class provides static methods to polyfill PHP 8.1 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php81
{
    /**
     * Used as a value for the image_type parameter of the image_type_to_mime_type()
     * and image_type_to_extension() functions to indicate AVIF image format.
     */
    public const IMAGETYPE_AVIF = 19;

    /**
     * AVIF image format bitmask for imagetypes().
     */
    public const IMG_AVIF = 256;

    /**
     * Used as a quality value for lossless WebP encoding.
     */
    public const IMG_WEBP_LOSSLESS = 101;

    /**
     * Replication refresh option for mysqli_refresh().
     * Indicates that the replica server should be refreshed.
     */
    public const MYSQLI_REFRESH_REPLICA = 64;

    /**
     * Synchronizes file data to disk.
     *
     * This is a best-effort polyfill for the fdatasync() function introduced in PHP 8.1.
     * On systems where the function is not available natively, this method attempts
     * to use fflush() to ensure data is written to PHP's buffer layer.
     *
     * Note: This is not a 100% accurate polyfill. For full functionality with
     * actual disk synchronization guarantees, use PHP 8.1+.
     *
     * @see https://www.php.net/manual/en/function.fdatasync.php
     *
     * @param resource $stream The file stream pointer
     * @return bool Returns true on success, false on failure
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function fdatasync($stream): bool
    {
        if (!is_resource($stream)) {
            return false;
        }

        // Flush PHP's internal buffers
        if (!fflush($stream)) {
            return false;
        }

        // If the posix extension is available, use it for actual disk sync
        if (function_exists('posix_fsync')) {
            return posix_fsync($stream);
        }

        return true;
    }

    /**
     * Synchronizes file changes to disk.
     *
     * This is a best-effort polyfill for the fsync() function introduced in PHP 8.1.
     * On systems where the function is not available natively, this method attempts
     * to use fflush() to ensure data is written to PHP's buffer layer.
     *
     * Note: This is not a 100% accurate polyfill. For full functionality with
     * actual disk synchronization guarantees, use PHP 8.1+.
     *
     * @see https://www.php.net/manual/en/function.fsync.php
     *
     * @param resource $stream The file stream pointer
     * @return bool Returns true on success, false on failure
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function fsync($stream): bool
    {
        if (!is_resource($stream)) {
            return false;
        }

        // Flush PHP's internal buffers
        if (!fflush($stream)) {
            return false;
        }

        // If the posix extension is available, use it for actual disk sync
        if (function_exists('posix_fsync')) {
            return posix_fsync($stream);
        }

        return true;
    }
}
