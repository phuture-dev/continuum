<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use RuntimeException;

/**
 * PHP 8.3 polyfill methods.
 *
 * This class provides static methods to polyfill PHP 8.3 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php83
{
    /**
     * Test for the existence of the file.
     */
    public const POSIX_F_OK = 0;

    /**
     * The minimum number of bytes of storage allocated for any portion of a file.
     */
    public const POSIX_PC_ALLOC_SIZE_MIN = 8;

    /**
     * If privileges are required to allow chown() to work.
     */
    public const POSIX_PC_CHOWN_RESTRICTED = 6;
    /**
     * The maximum number of links a given file or directory can have.
     */
    public const POSIX_PC_LINK_MAX = 0;

    /**
     * The maximum number of bytes in a terminal canonical input buffer.
     */
    public const POSIX_PC_MAX_CANON = 1;

    /**
     * The maximum number of bytes of a terminal input queue.
     */
    public const POSIX_PC_MAX_INPUT = 2;

    /**
     * The maximum number of characters for a file name alone, not its path.
     */
    public const POSIX_PC_NAME_MAX = 3;

    /**
     * If a file name longer than POSIX_PC_NAME_MAX causes an error.
     */
    public const POSIX_PC_NO_TRUNC = 7;

    /**
     * The maximum number of characters for a full path name.
     */
    public const POSIX_PC_PATH_MAX = 4;

    /**
     * The maximum number of bytes that can be written to a pipe in one operation.
     */
    public const POSIX_PC_PIPE_BUF = 5;

    /**
     * The maximum number of bytes in a symbolic link.
     */
    public const POSIX_PC_SYMLINK_MAX = 9;

    /**
     * Test for read permission.
     */
    public const POSIX_R_OK = 4;

    /**
     * Test for write permission.
     */
    public const POSIX_W_OK = 2;

    /**
     * Test for execute permission.
     */
    public const POSIX_X_OK = 1;

    /**
     * Checks effective access to a file.
     *
     * This is a stub method for the posix_eaccess() function introduced in PHP 8.3.
     * The actual functionality requires the POSIX extension and uses effective
     * user/group IDs for the check, which cannot be reliably polyfilled in userland.
     *
     * @see https://www.php.net/manual/en/function.posix-eaccess.php
     *
     * @param string $filename The path to check
     * @param int $mode The access mode (F_OK, R_OK, W_OK, X_OK)
     * @return bool Returns true if access is allowed, false otherwise
     * @throws RuntimeException If POSIX extension is not loaded
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function posix_eaccess(string $filename, int $mode = 0): bool
    {
        if (!extension_loaded('posix')) {
            throw new RuntimeException(
                'posix_eaccess() requires the POSIX extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

        return match ($mode) {
            self::POSIX_F_OK => file_exists($filename),
            self::POSIX_X_OK => is_executable($filename),
            self::POSIX_W_OK => is_writable($filename),
            self::POSIX_R_OK => is_readable($filename),
            default => false
        };
    }

    /**
     * Gets POSIX file path configuration values.
     *
     * This is a stub method for the posix_fpathconf() function introduced in PHP 8.3.
     * The actual functionality requires the POSIX extension and access to file
     * configuration values that cannot be reliably polyfilled in userland PHP.
     *
     * @see https://www.php.net/manual/en/function.posix-fpathconf.php
     *
     * @param resource $stream The file stream
     * @param int $name The configuration constant name
     * @return int|false Returns the configuration value, or false on failure
     * @throws RuntimeException If POSIX extension is not loaded
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function posix_fpathconf($stream, int $name): int|false
    {
        if (!extension_loaded('posix')) {
            throw new RuntimeException(
                'posix_fpathconf() requires the POSIX extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

        return false;
    }

    /**
     * Gets POSIX path configuration values.
     *
     * This is a stub method for the posix_pathconf() function introduced in PHP 8.3.
     * The actual functionality requires the POSIX extension and access to path
     * configuration values that cannot be reliably polyfilled in userland PHP.
     *
     * @see https://www.php.net/manual/en/function.posix-pathconf.php
     *
     * @param string $path The path to check
     * @param int $name The configuration constant name
     * @return int|false Returns the configuration value, or false on failure
     * @throws RuntimeException If POSIX extension is not loaded
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function posix_pathconf(string $path, int $name): int|false
    {
        if (!extension_loaded('posix')) {
            throw new RuntimeException(
                'posix_pathconf() requires the POSIX extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

        return false;
    }

    /**
     * Gets POSIX system configuration values.
     *
     * This is a stub method for the posix_sysconf() function introduced in PHP 8.3.
     * The actual functionality requires the POSIX extension and access to system
     * configuration values that cannot be reliably polyfilled in userland PHP.
     *
     * @see https://www.php.net/manual/en/function.posix-sysconf.php
     *
     * @param int $name The configuration constant name
     * @return int|false Returns the configuration value, or false on failure
     * @throws RuntimeException If POSIX extension is not loaded
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function posix_sysconf(int $name): int|false
    {
        if (!extension_loaded('posix')) {
            throw new RuntimeException(
                'posix_sysconf() requires the POSIX extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

        // Since we cannot access actual sysconf values from userland,
        // we return false to indicate unavailability
        return false;
    }

    /**
     * Checks if the socket is at the out-of-band mark.
     *
     * This is a stub method for the socket_atmark() function introduced in PHP 8.3.
     * The actual functionality requires the Socket extension and access to socket
     * state that cannot be reliably polyfilled in userland PHP.
     *
     * @see https://www.php.net/manual/en/function.socket-atmark.php
     *
     * @param resource $stream The socket stream
     * @return bool Returns true if at mark, false otherwise
     * @throws RuntimeException If Socket extension is not loaded
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function socket_atmark($stream): bool
    {
        if (!extension_loaded('sockets')) {
            throw new RuntimeException(
                'socket_atmark() requires the Sockets extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

        return false;
    }
}
