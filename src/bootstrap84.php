<?php

declare(strict_types=1);

use Phuture\Continuum\Php84;

if (\PHP_VERSION_ID >= 80400) {
    return;
}

/**
 * PHP 8.4 constants
 */
if (!defined('PHP_SBINDIR')) {
    define('PHP_SBINDIR', Php84::PHP_SBINDIR);
}

/**
 * PHP 8.4 functions
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
        return Php84::bcceil($num);
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
        return Php84::bcfloor($num);
    }
}

if (!function_exists('bcround')) {
    /**
     * Rounds a BCMath number to a specified precision.
     *
     * @param string $num The number to round as a string
     * @param int $precision The number of decimal digits to round to
     * @param RoundingMode|string $mode The rounding mode
     * @return string Returns the rounded number as a string
     */
    function bcround(
        string $num,
        int $precision = 0,
        RoundingMode|string $mode = RoundingMode::HalfAwayFromZero
    ): string {
        return Php84::bcround($num, $precision, $mode);
    }
}
