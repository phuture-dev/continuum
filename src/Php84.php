<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use RoundingMode;
use RuntimeException;

/**
 * PHP 8.4 polyfill methods.
 *
 * This class provides static methods to polyfill PHP 8.4 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php84
{
    /**
     * The system binary directory path.
     *
     * This constant provides a polyfill for the PHP_SBINDIR constant introduced in PHP 8.4.
     * The actual constant contains the path where system executables are installed.
     * Since this information is not available at runtime in older PHP versions,
     * this polyfill returns a common default path.
     *
     * @see https://www.php.net/manual/en/reserved.constants.php
     */
    public const PHP_SBINDIR = '/usr/local/sbin';

    /**
     * Rounds a BCMath number up to the nearest integer.
     *
     * This is a polyfill for the bcceil() function introduced in PHP 8.4.
     * It calculates the ceiling of a number represented as a string.
     *
     * @see https://www.php.net/manual/en/function.bcceil.php
     *
     * @param string $num The number to ceil as a string
     * @return string Returns the ceiling of num as a string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcceil(string $num): string
    {
        // Check if the number is already an integer
        if (str_contains($num, '.') === false) {
            return $num;
        }

        // Get the integer part
        $intPart = (string) intval($num);

        // If number is negative and has fractional part, we need to adjust
        if ($num < 0 && $num != $intPart) {
            // For negative numbers, ceiling means rounding toward zero
            // e.g., bcceil("-2.5") = "-2"
            return $intPart;
        }

        // If number is positive and has fractional part, add 1
        if ($num >= 0 && $num != $intPart) {
            return bcadd($intPart, '1', 0);
        }

        return $intPart;
    }

    /**
     * Rounds a BCMath number down to the nearest integer.
     *
     * This is a polyfill for the bcfloor() function introduced in PHP 8.4.
     * It calculates the floor of a number represented as a string.
     *
     * @see https://www.php.net/manual/en/function.bcfloor.php
     *
     * @param string $num The number to floor as a string
     * @return string Returns the floor of num as a string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcfloor(string $num): string
    {
        // Check if the number is already an integer
        if (str_contains($num, '.') === false) {
            return $num;
        }

        // Get the integer part
        $intPart = (string) intval($num);

        // If number is positive and has fractional part, return integer part
        if ($num >= 0) {
            return $intPart;
        }

        // If number is negative and has fractional part, subtract 1
        if ($num < 0 && $num != $intPart) {
            return bcsub($intPart, '1', 0);
        }

        return $intPart;
    }

    /**
     * Rounds a BCMath number to a specified precision.
     *
     * This is a polyfill for the bcround() function introduced in PHP 8.4.
     * It rounds a number represented as a string to a specified precision.
     *
     * @see https://www.php.net/manual/en/function.bcround.php
     *
     * @param string $num The number to round as a string
     * @param int $precision The number of decimal digits to round to (default: 0)
     * @param RoundingMode|string $mode The rounding mode (default: HalfAwayFromZero)
     * @return string Returns the rounded number as a string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcround(
        string $num,
        int $precision = 0,
        RoundingMode|string $mode = RoundingMode::HalfAwayFromZero
    ): string {
        // Calculate the scale factor: 10^|precision|
        $scaleFactor = bcpow('10', (string) abs($precision), 0);

        // Scale the number so we can round to an integer
        if ($precision >= 0) {
            // For positive precision: multiply by 10^precision
            // e.g., 1.15 with precision 1 becomes 11.5
            $scaled = bcmul($num, $scaleFactor, max($precision + 2, 10));
        } else {
            // For negative precision: divide by 10^|precision|
            // e.g., 115 with precision -1 becomes 11.5
            $scaled = bcdiv($num, $scaleFactor, max(-$precision + 2, 10));
        }

        // Get the integer and fractional parts of the scaled number
        $scaledParts = explode('.', (string) $scaled);
        $intPart = $scaledParts[0] ?? '0';
        $fracPart = $scaledParts[1] ?? '';

        // Determine the first decimal digit and whether there are more non-zero decimals
        $firstDecimal = isset($fracPart[0]) ? (int) $fracPart[0] : 0;
        $hasMoreDecimals = strlen($fracPart) > 1 && (int) substr($fracPart, 1) > 0;

        // Calculate the fractional value for rounding decision (0.0 to 1.0)
        $fractionFloat = $firstDecimal / 10.0;
        if ($hasMoreDecimals) {
            $fractionFloat += 0.000001; // Nudge up slightly to ensure proper rounding
        }

        $numFloat = (float) $num;

        // Apply the rounding mode to determine if we should round up
        $roundedInt = match ($mode) {
            RoundingMode::HalfAwayFromZero, 'HalfAwayFromZero' => ($fractionFloat >= 0.5)
                ? ($numFloat >= 0 ? bcadd($intPart, '1', 0) : bcsub($intPart, '1', 0))
                : $intPart,
            RoundingMode::HalfTowardsZero, 'HalfTowardsZero' => ($fractionFloat > 0.5)
                ? ($numFloat >= 0 ? bcadd($intPart, '1', 0) : bcsub($intPart, '1', 0))
                : $intPart,
            RoundingMode::HalfEven, 'HalfEven' => self::bcRoundHalfEven($numFloat, $intPart, $fractionFloat),
            RoundingMode::HalfOdd, 'HalfOdd' => self::bcRoundHalfOdd($numFloat, $intPart, $fractionFloat),
            RoundingMode::PositiveInfinity, 'PositiveInfinity' => self::bcRoundPositiveInfinity($numFloat, $intPart, $fractionFloat),
            RoundingMode::NegativeInfinity, 'NegativeInfinity' => self::bcRoundNegativeInfinity($numFloat, $intPart, $fractionFloat),
            RoundingMode::TowardsZero, 'TowardsZero' => $intPart,
            RoundingMode::AwayFromZero, 'AwayFromZero' => ($fractionFloat > 0.0)
                ? ($numFloat >= 0 ? bcadd($intPart, '1', 0) : bcsub($intPart, '1', 0))
                : $intPart,
            default => (string) round($numFloat, $precision),
        };

        // Scale back down to the desired precision
        $result = $roundedInt;
        if ($precision > 0) {
            $result = bcdiv($roundedInt, $scaleFactor, $precision);
        } elseif ($precision < 0) {
            $result = bcmul($roundedInt, $scaleFactor, 0);
        }

        // Normalize -0 to 0
        if ($result === '-0' || $result === '-0.0' || preg_match('/^-0\.0+$/', $result)) {
            return ltrim($result, '-');
        }

        return $result;
    }

    /**
     * Helper for rounding half to even (banker's rounding).
     *
     * @param float $num The original number being rounded
     * @param string $scaled The integer part as a string
     * @param float $fraction The fractional part
     * @return string The rounded result as a string
     */
    private static function bcRoundHalfEven(float $num, string $scaled, float $fraction): string
    {
        $absFraction = abs($fraction);

        if ($absFraction > 0.5) {
            return $num >= 0 ? bcadd($scaled, '1', 0) : bcsub($scaled, '1', 0);
        }

        if ($absFraction < 0.5) {
            return $scaled;
        }

        // Exactly 0.5 - round to nearest even
        $isEven = (intval($scaled) % 2 === 0);

        if ($isEven) {
            return $scaled;
        }

        return $num >= 0 ? bcadd($scaled, '1', 0) : bcsub($scaled, '1', 0);
    }

    /**
     * Helper for rounding half to odd.
     *
     * @param float $num The original number being rounded
     * @param string $scaled The integer part as a string
     * @param float $fraction The fractional part
     * @return string The rounded result as a string
     */
    private static function bcRoundHalfOdd(float $num, string $scaled, float $fraction): string
    {
        $absFraction = abs($fraction);

        if ($absFraction > 0.5) {
            return $num >= 0 ? bcadd($scaled, '1', 0) : bcsub($scaled, '1', 0);
        }

        if ($absFraction < 0.5) {
            return $scaled;
        }

        // Exactly 0.5 - round to nearest odd
        $isEven = (intval($scaled) % 2 === 0);

        if ($isEven) {
            return $num >= 0 ? bcadd($scaled, '1', 0) : bcsub($scaled, '1', 0);
        }

        return $scaled;
    }

    /**
     * Helper for rounding toward negative infinity (floor).
     *
     * @param float $num The original number being rounded
     * @param string $scaled The integer part as a string
     * @param float $fraction The fractional part
     * @return string The rounded result as a string
     */
    private static function bcRoundNegativeInfinity(float $num, string $scaled, float $fraction): string
    {
        // For negative infinity: round toward more negative values
        // Only round down (subtract 1) if the number is negative and has a fractional part
        if ($num < 0 && $fraction > 0.0) {
            return bcsub($scaled, '1', 0);
        }

        return $scaled;
    }

    /**
     * Helper for rounding toward positive infinity (ceil).
     *
     * @param float $num The original number being rounded
     * @param string $scaled The integer part as a string
     * @param float $fraction The fractional part
     * @return string The rounded result as a string
     */
    private static function bcRoundPositiveInfinity(float $num, string $scaled, float $fraction): string
    {
        // For positive infinity: round toward more positive values
        // Only round up (add 1) if the number is positive and has a fractional part
        if ($num >= 0 && $fraction > 0.0) {
            return bcadd($scaled, '1', 0);
        }

        return $scaled;
    }
}
