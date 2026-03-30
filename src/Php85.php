<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use Locale;
use Normalizer;

/**
 * PHP 8.5 polyfill methods.
 *
 * This class provides static methods to polyfill PHP 8.5 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php85
{
    /**
     * The PHP build date.
     *
     * This constant provides a polyfill for the PHP_BUILD_DATE constant introduced in PHP 8.5.
     * The actual constant contains the exact date and time when PHP was compiled.
     * Since this information is not available at runtime in older PHP versions,
     * this polyfill returns the release date of this package.
     *
     * @see https://www.php.net/manual/en/reserved.constants.php
     */
    public const PHP_BUILD_DATE = 'Dec 28 2025 00:00:00';

    /**
     * The build provider for this polyfill package.
     *
     * This constant identifies the source of this polyfill implementation.
     * It can be used to distinguish between native PHP 8.5+ and this polyfill.
     */
    public const PHP_BUILD_PROVIDER = 'phuture/continuum';

    /**
     * Mapping of language codes to their default RTL scripts.
     *
     * This mapping covers languages that primarily use RTL scripts.
     * Used when the script is not explicitly specified in the locale.
     *
     * @see https://unicode.org/iso15924/iso15924-codes.html
     *
     * @var array
     */
    private static array $rtlLanguages = [
        'ar' => 'Arab',  // Arabic
        'arc' => 'Armi', // Aramaic
        'arz' => 'Arab', // Egyptian Arabic
        'ckb' => 'Arab', // Central Kurdish (Sorani)
        'dv' => 'Thaa',  // Divehi/Maldivian
        'fa' => 'Arab',  // Persian
        'ha' => 'Arab',  // Hausa (when written in Arabic script, Ajami)
        'he' => 'Hebr',  // Hebrew
        'iw' => 'Hebr',  // Hebrew (old code)
        'khw' => 'Arab', // Khowar
        'ks' => 'Arab',  // Kashmiri
        'lrc' => 'Arab', // Northern Luri
        'mzn' => 'Arab', // Mazanderani
        'nqo' => 'Nkoo', // N'Ko (not in RTL_SCRIPTS but is RTL)
        'ota' => 'Arab', // Ottoman Turkish
        'pnb' => 'Arab', // Western Punjabi
        'ps' => 'Arab',  // Pashto
        'sd' => 'Arab',  // Sindhi
        'skr' => 'Arab', // Saraiki
        'syr' => 'Syrc', // Syriac
        'ug' => 'Arab',  // Uyghur
        'ur' => 'Arab',  // Urdu
        'yi' => 'Hebr'   // Yiddish
    ];

    /**
     * List of RTL (right-to-left) script codes from Unicode data.
     *
     * These script codes represent writing systems that are written from right to left.
     * Used by locale_is_right_to_left() to determine text direction.
     *
     * @see https://unicode.org/iso15924/iso15924-codes.html
     *
     * @var array
     */
    private static array $rtlScripts = [
        'Arab',  // Arabic
        'Aran',  // Arabic (Nastaliq variant)
        'Armi',  // Imperial Aramaic
        'Avst',  // Avestan
        'Chrs',  // Chorasmian
        'Cprt',  // Cypriot
        'Elym',  // Elymaic
        'Hatr',  // Hatran
        'Hebr',  // Hebrew
        'Hung',  // Old Hungarian
        'Khar',  // Kharoshthi
        'Lydi',  // Lydian
        'Mand',  // Mandaic
        'Mani',  // Manichaean
        'Merc',  // Meroitic Cursive
        'Mero',  // Meroitic Hieroglyphs
        'Narb',  // Old North Arabian
        'Nbat',  // Nabataean
        'Orkh',  // Old Turkic
        'Ougr',  // Old Uyghur
        'Palm',  // Palmyrene
        'Phli',  // Inscriptional Pahlavi
        'Phlp',  // Psalter Pahlavi
        'Phnx',  // Phoenician
        'Prti',  // Inscriptional Parthian
        'Rohg',  // Hanifi Rohingya
        'Samr',  // Samaritan
        'Sarb',  // Old South Arabian
        'Sogd',  // Sogdian
        'Sogo',  // Old Sogdian
        'Syrc',  // Syriac
        'Thaa',  // Thaana
        'Yezi'   // Yezidi
    ];

    /**
     * Calculates the Levenshtein distance between two strings using grapheme clusters.
     *
     * This polyfill implements the grapheme_levenshtein() function introduced in PHP 8.5.
     * Unlike the standard levenshtein() function which operates on bytes or characters,
     * this function operates on grapheme clusters, making it suitable for Unicode text
     * where a single user-perceived character may consist of multiple code points
     * (e.g., combining characters, emoji with skin tone modifiers).
     *
     * @see https://wiki.php.net/rfc/grapheme_levenshtein
     * @see https://www.php.net/manual/en/function.levenshtein.php
     *
     * @param string $string1 The first string (must be valid UTF-8)
     * @param string $string2 The second string (must be valid UTF-8)
     * @param int $insertion_cost The cost of inserting a grapheme cluster
     * @param int $replacement_cost The cost of replacing a grapheme cluster
     * @param int $deletion_cost The cost of deleting a grapheme cluster
     * @return int|false The Levenshtein distance, or false if either string is not valid UTF-8
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function grapheme_levenshtein(
        string $string1,
        string $string2,
        int $insertion_cost = 1,
        int $replacement_cost = 1,
        int $deletion_cost = 1
    ): int|false {
        // Split strings into grapheme clusters
        $clusters1 = self::splitGraphemes($string1);
        $clusters2 = self::splitGraphemes($string2);

        // If either string failed to parse as UTF-8, return false
        if ($clusters1 === false || $clusters2 === false) {
            return false;
        }

        $len1 = count($clusters1);
        $len2 = count($clusters2);

        // Handle edge cases
        if ($len1 === 0) {
            return $len2 * $insertion_cost;
        }
        if ($len2 === 0) {
            return $len1 * $deletion_cost;
        }

        // Initialize the distance matrix with only two rows for memory efficiency
        $prevRow = [];
        $currRow = [];

        // Initialize first row
        // phpcs:ignore
        for ($j = 0; $j <= $len2; ++$j) {
            $prevRow[$j] = $j * $insertion_cost;
        }

        // Fill the matrix
        // phpcs:ignore
        for ($i = 1; $i <= $len1; ++$i) {
            $currRow[0] = $i * $deletion_cost;

            // phpcs:ignore
            for ($j = 1; $j <= $len2; ++$j) {
                $cost = ($clusters1[$i - 1] === $clusters2[$j - 1]) ? 0 : $replacement_cost;

                $currRow[$j] = min(
                    $prevRow[$j] + $deletion_cost, // deletion
                    $currRow[$j - 1] + $insertion_cost, // insertion
                    $prevRow[$j - 1] + $cost // substitution
                );
            }

            // Swap rows
            $temp = $prevRow;
            $prevRow = $currRow;
            $currRow = $temp;
        }

        return $prevRow[$len2];
    }

    /**
     * Checks if a locale uses a right-to-left script.
     *
     * This polyfill implements the locale_is_right_to_left() function introduced in PHP 8.5.
     * It determines if the given locale uses a script that is written from right to left.
     *
     * @see https://wiki.php.net/rfc/locale_is_right_to_left
     * @see https://www.php.net/manual/en/function.locale-is-right-to-left.php
     *
     * @param string $locale The locale identifier (e.g., 'ar', 'he', 'en')
     * @return bool True if the locale uses an RTL script, false otherwise
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function locale_is_right_to_left(string $locale): bool
    {
        // First, check if the script is explicitly specified in the locale
        $script = Locale::getScript($locale);

        if ($script !== null && $script !== '') {
            return in_array($script, self::$rtlScripts, true);
        }

        // If no explicit script, check the language mapping
        $language = Locale::getPrimaryLanguage($locale);

        if ($language !== null && $language !== '') {
            // Check if the language has a default RTL script
            if (isset(self::$rtlLanguages[$language])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Splits a UTF-8 string into an array of grapheme clusters.
     *
     * Strings are normalized to NFC form before splitting to ensure that
     * equivalent grapheme clusters (e.g., precomposed vs decomposed forms)
     * are compared as equal.
     *
     * @param string $string The string to split
     * @return array|false Array of grapheme clusters, or false on UTF-8 error
     */
    private static function splitGraphemes(string $string): array|false
    {
        if ($string === '') {
            return [];
        }

        // Normalize to NFC to ensure equivalent grapheme clusters compare as equal
        // This handles cases like e + combining acute (NFD) vs precomposed é (NFC)
        // Use FORM_C for compatibility with PHP < 8.3 (FORM_NFC was added in PHP 8.3)
        $string = Normalizer::normalize($string, Normalizer::FORM_C);
        if ($string === false) {
            return false;
        }

        // Check if the string is valid UTF-8 using grapheme_strlen
        // grapheme_strlen returns false on invalid UTF-8
        $len = grapheme_strlen($string);
        if ($len === false) {
            return false;
        }

        $clusters = [];
        $pos = 0;

        // phpcs:ignore
        for ($i = 0; $i < $len; ++$i) {
            $cluster = grapheme_extract($string, 1, GRAPHEME_EXTR_COUNT, $pos, $nextPos);
            if ($cluster === false) {
                return false;
            }
            $clusters[] = $cluster;
            $pos = $nextPos;
        }

        return $clusters;
    }
}
