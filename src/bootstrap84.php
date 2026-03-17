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
if (extension_loaded('bcmath')) {
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
         * @param RoundingMode|int $mode The rounding mode
         * @return string Returns the rounded number as a string
         */
        function bcround(string $num, int $precision = 0, RoundingMode|int $mode = 1): string
        {
            return Php84::bcround($num, $precision, $mode);
        }
    }
}

if (!function_exists('request_parse_body')) {
    /**
     * Parses the request body.
     *
     * @param mixed $contentNegotiation Content negotiation settings
     * @return array Returns an array with parsed data
     */
    function request_parse_body(mixed $contentNegotiation = null): array
    {
        return Php84::request_parse_body($contentNegotiation);
    }
}

if (extension_loaded('ldap')) {
    if (!function_exists('ldap_exop')) {
        /**
         * Performs an LDAP extended operation.
         *
         * @param mixed $ldap LDAP link identifier
         * @param string $requestoid The extended operation request OID
         * @param string|null $requestdata The extended operation request data
         * @param string|null $responseoid The response OID (output)
         * @param string|null $responsedata The response data (output)
         * @return bool Returns true on success, false on failure
         */
        function ldap_exop(
            mixed $ldap,
            string $requestoid,
            ?string $requestdata = null,
            ?string &$responseoid = null,
            ?string &$responsedata = null
        ): bool {
            return Php84::ldap_exop($ldap, $requestoid, $requestdata, $responseoid, $responsedata);
        }
    }

    if (!function_exists('ldap_parse_exop')) {
        /**
         * Parses an LDAP extended operation result.
         *
         * @param mixed $ldap LDAP link identifier
         * @param mixed $result Result identifier
         * @param string|null $responseoid The response OID (output)
         * @param string|null $responsedata The response data (output)
         * @return bool Returns true on success, false on failure
         */
        function ldap_parse_exop(
            mixed $ldap,
            mixed $result,
            ?string &$responseoid = null,
            ?string &$responsedata = null
        ): bool {
            return Php84::ldap_parse_exop($ldap, $result, $responseoid, $responsedata);
        }
    }
}
