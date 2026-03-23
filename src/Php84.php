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
        if (!extension_loaded('bcmath')) {
            throw new RuntimeException(
                'bcceil() requires the BCMath extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

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
        if (!extension_loaded('bcmath')) {
            throw new RuntimeException(
                'bcfloor() requires the BCMath extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

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
        RoundingMode|string $mode = 'HalfAwayFromZero'
    ): string {
        if (!extension_loaded('bcmath')) {
            throw new RuntimeException(
                'bcround() requires the BCMath extension. ' .
                'This function cannot be polyfilled without it.'
            );
        }

        // Calculate the scale factor for the given precision
        if ($precision < 0) {
            $scaleFactor = bcpow('10', (string) -$precision, 0);
        } else {
            $scaleFactor = '1.' . str_repeat('0', $precision);
            if ($precision === 0) {
                $scaleFactor = '1';
            }
        }

        // Scale the number
        $scaled = bcmul($num, $scaleFactor, $precision + 2);

        // Get the integer part and fractional part
        $scaledParts = explode('.', (string) $scaled);
        $intPart = $scaledParts[0] ?? '0';
        $fracPart = $scaledParts[1] ?? '';

        // Determine the fractional value for rounding
        if ($precision === 0) {
            // For precision 0, check if we need to round up
            $fraction = $fracPart !== '' ? '0.' . $fracPart : '0';
            $fractionFloat = (float) $fraction;
        } else {
            // For non-zero precision, we need to look at the digit after precision
            $fracLen = strlen($fracPart);
            if ($fracLen > $precision) {
                $roundDigit = (int) $fracPart[$precision];
                $rest = substr($fracPart, $precision + 1);

                // Determine if we need to round up
                $fractionFloat = $roundDigit / 10.0;

                if ($rest !== '' && (int) $rest > 0) {
                    $fractionFloat += 0.000001; // Nudge up for proper rounding
                }
            } else {
                $fractionFloat = 0.0;
            }

            $intPart = substr($intPart . $fracPart, 0, strlen($intPart) + $precision);
        }

        $numFloat = (float) $num;

        // Match by constant name for readability
        return match ($mode) {
            RoundingMode::HalfAwayFromZero, 'HalfAwayFromZero' => ($fractionFloat >= 0.5)
                ? ($numFloat >= 0 ? bcadd($intPart, '1', 0) : bcsub($intPart, '1', 0))
                : $intPart,
            RoundingMode::HalfTowardsZero, 'HalfTowardsZero' => ($fractionFloat > 0.5)
                ? ($numFloat >= 0 ? bcadd($intPart, '1', 0) : bcsub($intPart, '1', 0))
                : $intPart,
            RoundingMode::HalfEven, 'HalfEven' => self::bcRoundHalfEven($numFloat, $intPart, $fractionFloat),
            RoundingMode::HalfOdd, 'HalfOdd' => self::bcRoundHalfOdd($numFloat, $intPart, $fractionFloat),
            RoundingMode::PositiveInfinity, 'PositiveInfinity' => self::bcRoundPositiveInfinity($intPart, $fractionFloat),
            RoundingMode::NegativeInfinity, 'NegativeInfinity' => self::bcRoundNegativeInfinity($intPart, $fractionFloat),
            RoundingMode::TowardsZero, 'TowardsZero' => $intPart,
            RoundingMode::AwayFromZero, 'AwayFromZero' => ($fractionFloat > 0.0)
                ? ($numFloat >= 0 ? bcadd($intPart, '1', 0) : bcsub($intPart, '1', 0))
                : $intPart,
            default => round($numFloat, $precision) . '',
        };
    }

    /**
     * LDAP extended operation wrapper.
     *
     * This is a stub method for LDAP functions in PHP 8.4.
     * The actual functionality requires the LDAP extension and PHP 8.4+.
     *
     * @see https://www.php.net/manual/en/function.ldap-exop.php
     *
     * @param mixed $ldap LDAP link identifier
     * @param string $requestoid The extended operation request OID
     * @param string|null $requestdata The extended operation request data
     * @param string|null $responseoid The response OID (output)
     * @param string|null $responsedata The response data (output)
     * @return bool Returns true on success, false on failure
     * @throws RuntimeException Always throws as this requires PHP 8.4+ with LDAP extension
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function ldap_exop(
        $ldap,
        string $requestoid,
        ?string $requestdata = null,
        ?string &$responseoid = null,
        ?string &$responsedata = null
    ): bool {
        throw new RuntimeException(
            'ldap_exop() requires PHP 8.4+ with LDAP extension. ' .
            'This function cannot be polyfilled in userland PHP.'
        );
    }

    /**
     * LDAP parse extended operation result.
     *
     * This is a stub method for LDAP functions in PHP 8.4.
     * The actual functionality requires PHP 8.4+ with LDAP extension.
     *
     * @see https://www.php.net/manual/en/function.ldap-parse-exop.php
     *
     * @param mixed $ldap LDAP link identifier
     * @param mixed $result Result
     * @param string|null $responseoid The response OID (output)
     * @param string|null $responsedata The response data (output)
     * @return bool Returns true on success, false on failure
     * @throws RuntimeException Always throws as this requires PHP 8.4+ with LDAP extension
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function ldap_parse_exop(
        $ldap,
        $result,
        ?string &$responseoid = null,
        ?string &$responsedata = null
    ): bool {
        throw new RuntimeException(
            'ldap_parse_exop() requires PHP 8.4+ with LDAP extension. ' .
            'This function cannot be polyfilled in userland PHP.'
        );
    }

    /**
     * Parses the request body.
     *
     * This is a stub method for the request_parse_body() function introduced in PHP 8.4.
     * The actual functionality requires access to internal PHP request state that
     * cannot be reliably replicated in userland PHP.
     *
     * @see https://www.php.net/manual/en/function.request-parse-body.php
     *
     * @param mixed $contentNegotiation Content negotiation settings
     * @return array Returns an array with parsed data
     * @throws RuntimeException Always throws as this requires PHP 8.4+ internal state
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function request_parse_body($contentNegotiation = null): array
    {
        throw new RuntimeException(
            'request_parse_body() requires PHP 8.4+ and access to internal ' .
            'request state. This function cannot be polyfilled in userland PHP.'
        );
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
     * @param string $scaled The integer part as a string
     * @param float $fraction The fractional part
     * @return string The rounded result as a string
     */
    private static function bcRoundNegativeInfinity(string $scaled, float $fraction): string
    {
        if ($fraction < 0.0) {
            return bcsub($scaled, '1', 0);
        }

        return $scaled;
    }

    /**
     * Helper for rounding toward positive infinity (ceil).
     *
     * @param string $scaled The integer part as a string
     * @param float $fraction The fractional part
     * @return string The rounded result as a string
     */
    private static function bcRoundPositiveInfinity(string $scaled, float $fraction): string
    {
        if ($fraction > 0.0) {
            return bcadd($scaled, '1', 0);
        }

        return $scaled;
    }
}
