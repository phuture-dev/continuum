<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Phuture\Continuum\Extension\Mbstring;

if (extension_loaded('mbstring')) {
    return;
}

/**
 * Mbstring functions
 */
if (!function_exists('mb_convert_kana')) {
    /**
     * Converts between half-width and full-width Japanese characters.
     *
     * @param string $string The string to convert
     * @param string $mode The conversion mode (default: 'KV')
     * @param string|null $encoding The character encoding (not used in this polyfill)
     * @return string The converted string
     */
    function mb_convert_kana(string $string, string $mode = 'KV', ?string $encoding = null): string
    {
        return Mbstring::mb_convert_kana($string, $mode, $encoding);
    }
}

if (!function_exists('mb_preferred_mime_name')) {
    /**
     * Gets the preferred MIME name for a given encoding.
     *
     * @param string $encoding The character encoding to look up
     * @return string The preferred MIME name
     */
    function mb_preferred_mime_name(string $encoding): string
    {
        return Mbstring::mb_preferred_mime_name($encoding);
    }
}

if (!function_exists('mb_send_mail')) {
    /**
     * Sends email with proper encoding for multibyte characters.
     *
     * @param string $to The recipient email address
     * @param string $subject The email subject
     * @param string $message The email message body
     * @param array|string $additional_headers Additional headers (string or array)
     * @param string|null $additional_params Additional parameters to pass to mail()
     * @return bool Returns true if the mail was accepted for delivery, false otherwise
     */
    function mb_send_mail(
        string $to,
        string $subject,
        string $message,
        array|string $additional_headers = [],
        ?string $additional_params = null
    ): bool {
        return Mbstring::mb_send_mail($to, $subject, $message, $additional_headers, $additional_params);
    }
}

if (!function_exists('mb_split')) {
    /**
     * Splits a multibyte string using a regular expression.
     *
     * @param string $pattern The regular expression pattern
     * @param string $string The string to split
     * @param int $limit The maximum number of elements to return (-1 for no limit)
     * @return array|false An array of substrings, or false on failure
     */
    function mb_split(string $pattern, string $string, int $limit = -1): array|false
    {
        return Mbstring::mb_split($pattern, $string, $limit);
    }
}

if (!function_exists('mb_strcut')) {
    /**
     * Gets a portion of a multibyte string while maintaining multibyte safety.
     *
     * @param string $string The input string
     * @param int $start The starting position in bytes
     * @param int|null $length The length in bytes (null for rest of string)
     * @param string|null $encoding The character encoding
     * @return string The extracted portion of the string
     */
    function mb_strcut(string $string, int $start, ?int $length = null, ?string $encoding = null): string
    {
        return Mbstring::mb_strcut($string, $start, $length, $encoding);
    }
}

if (!function_exists('mb_strimwidth')) {
    /**
     * Truncates a multibyte string to a specified width.
     *
     * @param string $string The string to truncate
     * @param int $start The starting position
     * @param int $width The desired width in characters
     * @param string $trim_marker The string to append when truncating
     * @param string|null $encoding The character encoding
     * @return string The truncated string
     */
    function mb_strimwidth(
        string $string,
        int $start,
        int $width,
        string $trim_marker = '',
        ?string $encoding = null
    ): string {
        return Mbstring::mb_strimwidth($string, $start, $width, $trim_marker, $encoding);
    }
}
