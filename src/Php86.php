<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use ValueError;

/**
 * PHP 8.6 polyfill methods.
 *
 * This class provides static methods to polyfill PHP 8.6 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php86
{
    /**
     * Clamps a value between a minimum and a maximum bound.
     *
     * This method provides a polyfill for the clamp() function introduced in PHP 8.6.
     * If the value is greater than the maximum, the maximum is returned; if it is
     * smaller than the minimum, the minimum is returned; otherwise the value is
     * returned unchanged.
     *
     * @see https://wiki.php.net/rfc/clamp
     *
     * @param mixed $value The value to clamp
     * @param mixed $min The lower bound
     * @param mixed $max The upper bound
     * @return mixed Returns value if it is within the range, otherwise the nearest bound
     * @throws ValueError If min or max is NAN, or if min is greater than max
     */
    public static function clamp(mixed $value, mixed $min, mixed $max): mixed
    {
        if (\is_float($min) && \is_nan($min)) {
            throw new ValueError('clamp(): Argument #2 ($min) must not be NAN');
        }

        if (\is_float($max) && \is_nan($max)) {
            throw new ValueError('clamp(): Argument #3 ($max) must not be NAN');
        }

        if ($max < $min) {
            throw new ValueError('clamp(): Argument #2 ($min) must be smaller than or equal to argument #3 ($max)');
        }

        if ($value > $max) {
            return $max;
        }

        if ($value < $min) {
            return $min;
        }

        return $value;
    }

    /**
     * Reverses a string in grapheme units.
     *
     * This method provides a polyfill for the grapheme_strrev() function introduced
     * in PHP 8.6. Unlike strrev(), which reverses a string byte by byte, this
     * method reverses complete grapheme clusters, keeping multi-byte characters,
     * combining sequences, and emoji with modifiers intact.
     *
     * @see https://php.watch/versions/8.6/grapheme_strrev
     *
     * @param string $string The string to reverse
     * @return string|false Returns the reversed string, or false on failure
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function grapheme_strrev(string $string): string|false
    {
        $units = grapheme_str_split($string);
        if ($units === false) {
            return false;
        }

        return implode('', array_reverse($units));
    }
}
