<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Phuture\Continuum\Php86;

if (\PHP_VERSION_ID >= 80600) {
    return;
}

/**
 * PHP 8.6 functions
 */
if (!function_exists('clamp')) {
    /**
     * Clamps a value between a minimum and a maximum bound.
     *
     * @param mixed $value The value to clamp
     * @param mixed $min The lower bound
     * @param mixed $max The upper bound
     * @return mixed Returns value if it is within the range, otherwise the nearest bound
     * @throws ValueError If min or max is NAN, or if min is greater than max
     */
    function clamp(mixed $value, mixed $min, mixed $max): mixed
    {
        return Php86::clamp($value, $min, $max);
    }
}

if (!function_exists('grapheme_strrev')) {
    /**
     * Reverses a string in grapheme units.
     *
     * @param string $string The string to reverse
     * @return string|false Returns the reversed string, or false on failure
     */
    function grapheme_strrev(string $string): string|false
    {
        return Php86::grapheme_strrev($string);
    }
}
