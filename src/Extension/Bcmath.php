<?php

declare(strict_types=1);

namespace Phuture\Continuum\Extension;

use RoundingMode;
use Symfony\Polyfill\Php84 as Symfony;

/**
 * BCMath polyfill methods.
 *
 * This class provides static methods to polyfill BCMath functions that are
 * not available in older PHP versions or when the bcmath extension is not loaded.
 * Each method delegates to Symfony's Php84 polyfill.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Bcmath
{
    /**
     * Rounds a BCMath number up to the nearest integer.
     *
     * @param string $num The number to ceil as a string
     * @return string Returns the ceiling of num as a string
     */
    public static function bcceil(string $num): string
    {
        return Symfony\Php84::bcceil($num);
    }

    /**
     * Performs integer division on two BCMath numbers and returns the quotient and remainder.
     *
     * @param string $num1 The dividend as a string
     * @param string $num2 The divisor as a string
     * @param int|null $scale The optional scale parameter
     * @return array|null Returns an array with the quotient and remainder, or null on failure
     */
    public static function bcdivmod(string $num1, string $num2, ?int $scale = null): ?array
    {
        return Symfony\Php84::bcdivmod($num1, $num2, $scale);
    }

    /**
     * Rounds a BCMath number down to the nearest integer.
     *
     * @param string $num The number to floor as a string
     * @return string Returns the floor of num as a string
     */
    public static function bcfloor(string $num): string
    {
        return Symfony\Php84::bcfloor($num);
    }

    /**
     * Rounds a BCMath number to a specified precision.
     *
     * @param string $num The number to round as a string
     * @param int $precision The number of decimal digits to round to
     * @param RoundingMode|int|string $mode The rounding mode
     * @return string Returns the rounded number as a string
     */
    public static function bcround(string $num, int $precision = 0, $mode = RoundingMode::HalfAwayFromZero): string
    {
        return Symfony\Php84::bcround($num, $precision, $mode);
    }
}
