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
    public const IMG_AVIF = 19;

    /**
     * Used as a quality value for lossless WebP encoding.
     */
    public const IMG_WEBP_LOSSLESS = 18;

    /**
     * Replication refresh option for mysqli_refresh().
     * Indicates that the replica server should be refreshed.
     */
    public const MYSQLI_REFRESH_REPLICA = 64;

    /**
     * Token for the readonly keyword.
     */
    public const T_READONLY = 384;

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

        // fdatasync is similar to fsync but doesn't sync metadata
        // In our polyfill, we treat them the same
        $result = fflush($stream);

        return $result;
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

        // Flush the output buffer to PHP's stream layer
        $result = fflush($stream);

        // On systems where we can't do actual fsync, we return the fflush result
        // This provides best-effort behavior but doesn't guarantee disk sync
        return $result;
    }
}
