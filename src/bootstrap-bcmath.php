<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Symfony\Polyfill\Php84 as Symfony;

if (extension_loaded('bcmath') && \PHP_VERSION_ID >= 80400) {
    return;
}

/**
 * BCMath functions
 */
if (!function_exists('bcceil')) {
    /**
     * Rounds a BCMath number up to the nearest integer.
     *
     * @param string $num The number to ceil as a string
     * @return string Returns the ceiling of num as a string
     */
    function bcceil(string $num): string
    {
        return Symfony\Php84::bcceil($num);
    }
}

if (!function_exists('bcdivmod')) {
    /**
     * Performs integer division on two BCMath numbers and returns the quotient and remainder.
     *
     * @param string $num1 The dividend as a string
     * @param string $num2 The divisor as a string
     * @param int|null $scale The optional scale parameter
     * @return array|null Returns an array with the quotient and remainder, or null on failure
     */
    function bcdivmod(string $num1, string $num2, ?int $scale = null): ?array
    {
        return Symfony\Php84::bcdivmod($num1, $num2, $scale);
    }
}

if (!function_exists('bcfloor')) {
    /**
     * Rounds a BCMath number down to the nearest integer.
     *
     * @param string $num The number to floor as a string
     * @return string Returns the floor of num as a string
     */
    function bcfloor(string $num): string
    {
        return Symfony\Php84::bcfloor($num);
    }
}

if (!function_exists('bcround')) {
    /**
     * Rounds a BCMath number to a specified precision.
     *
     * @param string $num The number to round as a string
     * @param int $precision The number of decimal digits to round to
     * @param RoundingMode|int|string $mode The rounding mode
     * @return string Returns the rounded number as a string
     */
    function bcround(string $num, int $precision = 0, $mode = RoundingMode::HalfAwayFromZero): string
    {
        return Symfony\Php84::bcround($num, $precision, $mode);
    }
}
