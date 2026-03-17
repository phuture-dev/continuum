<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use Error;
use Normalizer;
use CurlMultiHandle;
use RuntimeException;
use ReflectionProperty;
use ReflectionException;

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
     * Flag to make filter_var() throw ValueError on failure.
     *
     * This constant was introduced in PHP 8.5 for filter_var() and related functions.
     * When this flag is used, filter functions will throw a ValueError exception
     * instead of returning false on validation failure.
     *
     * @see https://www.php.net/manual/en/function.filter-var.php
     */
    public const FILTER_THROW_ON_FAILURE = 0x40000000;

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
     * Clones an object and updates its properties.
     *
     * This polyfill implements the clone() function introduced in PHP 8.5.
     * It allows cloning an object and updating its properties in a single operation,
     * which is especially useful for the "with-er" pattern in readonly classes.
     *
     * @see https://wiki.php.net/rfc/clone_with_v2
     * @see https://www.php.net/manual/en/function.clone.php
     *
     * @param object $object The object to clone
     * @param array $properties Associative array of properties to override (key => value)
     * @return object The cloned object with updated properties
     * @throws RuntimeException If attempting to modify readonly properties in PHP < 8.3
     * @throws ReflectionException If a property doesn't exist
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function clone(object $object, array $properties = []): object
    {
        $cloned = clone $object;

        if (empty($properties)) {
            return $cloned;
        }

        foreach ($properties as $name => $value) {
            try {
                $cloned->{$name} = $value;
            } catch (Error $e) {
                $reflection = new ReflectionProperty($object, $name);

                if (method_exists($reflection, 'isReadOnly') && $reflection->isReadOnly() && PHP_VERSION_ID < 80300) {
                    throw new RuntimeException(
                        "Cannot modify readonly property {$reflection->getDeclaringClass()->getName()}::\${$name} " .
                        "in PHP < 8.3. The clone() polyfill requires PHP 8.3+ to modify readonly properties."
                    );
                }

                $reflection->setAccessible(true);
                $reflection->setValue($cloned, $value);
            }
        }

        return $cloned;
    }

    /**
     * Retrieves all curl handles associated with a cURL multi handle.
     *
     * This is a stub method for the curl_multi_get_handles() function introduced in PHP 8.5.
     * The actual functionality requires PHP 8.5+ because PHP does not expose the internal
     * handle tracking mechanism of curl multi handles to userland code.
     *
     * @see https://www.php.net/manual/en/function.curl-multi-get-handles.php
     *
     * @param resource|CurlMultiHandle $multiHandle The cURL multi handle
     * @return array Array of CurlHandle objects
     * @throws RuntimeException Always throws as this requires PHP 8.5+ curl internals
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function curl_multi_get_handles($multiHandle): array
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException(
                'curl_multi_get_handles() requires the cURL extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

        throw new RuntimeException(
            'curl_multi_get_handles() requires PHP 8.5+ and access to internal ' .
            'cURL multi handle tracking. This function cannot be polyfilled in userland PHP. ' .
            'You must track curl handles yourself when using curl_multi_add_handle().'
        );
    }

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
        for ($j = 0; $j <= $len2; ++$j) {
            $prevRow[$j] = $j * $insertion_cost;
        }

        // Fill the matrix
        for ($i = 1; $i <= $len1; ++$i) {
            $currRow[0] = $i * $deletion_cost;

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
