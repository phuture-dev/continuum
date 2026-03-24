<?php

declare(strict_types=1);

use Phuture\Continuum\Php85;

if (\PHP_VERSION_ID >= 80500) {
    return;
}

/**
 * PHP 8.5 constants
 */
if (!defined('PHP_BUILD_DATE')) {
    define('PHP_BUILD_DATE', Php85::PHP_BUILD_DATE);
}

if (!defined('PHP_BUILD_PROVIDER')) {
    define('PHP_BUILD_PROVIDER', Php85::PHP_BUILD_PROVIDER);
}

/**
 * PHP 8.5 functions
 */
if (!function_exists('grapheme_levenshtein')) {
    /**
     * Calculates the Levenshtein distance between two strings using grapheme clusters.
     *
     * @param string $string1 The first string (must be valid UTF-8)
     * @param string $string2 The second string (must be valid UTF-8)
     * @param int $insertion_cost The cost of inserting a grapheme cluster
     * @param int $replacement_cost The cost of replacing a grapheme cluster
     * @param int $deletion_cost The cost of deleting a grapheme cluster
     * @return int|false The Levenshtein distance, or false if either string is not valid UTF-8
     */
    function grapheme_levenshtein(
        string $string1,
        string $string2,
        int $insertion_cost = 1,
        int $replacement_cost = 1,
        int $deletion_cost = 1
    ): int|false {
        return Php85::grapheme_levenshtein($string1, $string2, $insertion_cost, $replacement_cost, $deletion_cost);
    }
}

if (!function_exists('locale_is_right_to_left')) {
    /**
     * Checks if a locale uses a right-to-left script.
     *
     * @param string $locale The locale identifier (e.g., 'ar', 'he', 'en')
     * @return bool True if the locale uses an RTL script, false otherwise
     */
    function locale_is_right_to_left(string $locale): bool
    {
        return Php85::locale_is_right_to_left($locale);
    }
}
