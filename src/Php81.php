<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use RuntimeException;

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
     * Compresses data using Brotli encoding.
     *
     * This is a stub method for brotli_compress() functionality.
     * The actual functionality requires the Brotli extension.
     *
     * @see https://www.php.net/manual/en/function.brotli-compress.php
     *
     * @param string $data The data to compress
     * @param int $encoding The encoding mode
     * @param int $quality The compression quality (0-11)
     * @return string|false Returns compressed data or false on failure
     * @throws RuntimeException Always throws as this requires Brotli extension
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function brotli_compress(string $data, int $encoding = 0, int $quality = 11)
    {
        throw new RuntimeException(
            'Brotli compression requires the Brotli extension. ' .
            'This function cannot be polyfilled in userland PHP.'
        );
    }

    /**
     * Decompresses Brotli compressed data.
     *
     * This is a stub method for brotli_decompress() functionality.
     * The actual functionality requires the Brotli extension.
     *
     * @see https://www.php.net/manual/en/function.brotli-decompress.php
     *
     * @param string $data The compressed data
     * @param int $max_length Maximum length of decompressed data
     * @return string|false Returns decompressed data or false on failure
     * @throws RuntimeException Always throws as this requires Brotli extension
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function brotli_decompress(string $data, int $max_length = 0)
    {
        throw new RuntimeException(
            'Brotli decompression requires the Brotli extension. ' .
            'This function cannot be polyfilled in userland PHP.'
        );
    }

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

    /**
     * Output an AVIF image to browser or file.
     *
     * This is a stub method for the imageavif() function introduced in PHP 8.1.
     * The actual functionality requires GD library with AVIF support, which cannot be
     * polyfilled in pure userland PHP.
     *
     * @see https://www.php.net/manual/en/function.imageavif.php
     *
     * @param resource $image An image resource returned by imagecreatetruecolor()
     * @param resource|string|null $file The path or stream to save the file to, or null to output
     * @param int $quality Compression quality (0-100), default -1 (default library quality)
     * @param int $speed Encoding speed (0-10), default -1 (default library speed)
     * @return bool Returns true on success, false on failure
     * @throws RuntimeException Always throws as this requires GD with AVIF support
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function imageavif($image, $file = null, int $quality = -1, int $speed = -1): bool
    {
        throw new RuntimeException(
            'imageavif() requires GD library with AVIF support. ' .
            'This function cannot be polyfilled in userland PHP. ' .
            'Use PHP 8.1+ with GD compiled against libavif.'
        );
    }

    /**
     * Creates a new image from AVIF file.
     *
     * This is a stub method for the imagecreatefromavif() function introduced in PHP 8.1.
     * The actual functionality requires GD library with AVIF support, which cannot be
     * polyfilled in pure userland PHP.
     *
     * @see https://www.php.net/manual/en/function.imagecreatefromavif.php
     *
     * @param string $filename Path to the AVIF file
     * @return resource|false Returns an image resource identifier, or false on failure
     * @throws RuntimeException Always throws as this requires GD with AVIF support
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function imagecreatefromavif(string $filename)
    {
        throw new RuntimeException(
            'imagecreatefromavif() requires GD library with AVIF support. ' .
            'This function cannot be polyfilled in userland PHP. ' .
            'Use PHP 8.1+ with GD compiled against libavif.'
        );
    }
}
