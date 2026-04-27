<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use LogicException;

/**
 * PHP 6.0 polyfill methods. ;-)
 *
 * This class provides static methods to polyfill PHP 6.0 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php60
{
    /**
     * Flag to skip conversion errors.
     *
     * This constant was intended for PHP 6's unicode_decode() function.
     *
     * @var int
     */
    public const U_CONV_ERROR_SKIP = 0;

    /**
     * Decodes a Unicode string to the specified encoding.
     *
     * @see https://wiki.php.net/rfc/unicode
     *
     * @param string $string The Unicode string to decode
     * @param string $encoding The target encoding (default: 'UTF-8')
     * @param int $flags Conversion flags (default: U_CONV_ERROR_SKIP)
     * @return mixed The decoded string
     * @throws LogicException Always throws as this function cannot be polyfilled
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function unicode_decode(
        string $string,
        string $encoding = 'UTF-8',
        int $flags = self::U_CONV_ERROR_SKIP
    ): mixed {
        throw new LogicException(
            'unicode_decode() is a myth, much like PHP 6 itself. ' .
            'Some dreams are better left unfulfilled.'
        );
    }
}
