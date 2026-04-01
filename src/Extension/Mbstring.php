<?php

declare(strict_types=1);

namespace Phuture\Continuum\Extension;

/**
 * Mbstring polyfill methods.
 *
 * This class provides static methods to polyfill mbstring functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are minimal best-effort implementations.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Mbstring
{
    /**
     * The current character encoding for multibyte regex operations.
     *
     * @var string
     */
    private static string $regexEncoding = 'UTF-8';

    /**
     * The current options for multibyte regex operations.
     *
     * @var string
     */
    private static string $regexOptions = 'kr';

    /**
     * Converts between half-width and full-width Japanese characters.
     *
     * This is a minimal best-effort polyfill for the mb_convert_kana() function.
     * It provides basic conversion between half-width and full-width forms for
     * alphanumeric characters and katakana.
     *
     * Note: This is a minimal implementation and does not cover all conversion modes
     * and special cases supported by the native mbstring extension. For production use
     * with Japanese text, use the native mbstring extension.
     *
     * @see https://www.php.net/manual/en/function.mb-convert-kana.php
     *
     * @param string $string The string to convert
     * @param string $mode The conversion mode (default: 'KV')
     * @param string|null $encoding The character encoding (not used in this polyfill)
     * @return string The converted string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_convert_kana(string $string, string $mode = 'KV', ?string $encoding = null): string
    {
        // Basic mapping of half-width to full-width and vice versa.
        // This is a minimal best-effort polyfill.
        $map = [
            'A' => [
                '0' => '０',
                '1' => '１',
                '2' => '２',
                '3' => '３',
                '4' => '４',
                '5' => '５',
                '6' => '６',
                '7' => '７',
                '8' => '８',
                '9' => '９',
                'a' => 'ａ',
                'b' => 'ｂ',
                'c' => 'ｃ',
                'd' => 'ｄ',
                'e' => 'ｅ',
                'f' => 'ｆ',
                'g' => 'ｇ',
                'h' => 'ｈ',
                'i' => 'ｉ',
                'j' => 'ｊ',
                'k' => 'ｋ',
                'l' => 'ｌ',
                'm' => 'ｍ',
                'n' => 'ｎ',
                'o' => 'ｏ',
                'p' => 'ｐ',
                'q' => 'ｑ',
                'r' => 'ｒ',
                's' => 'ｓ',
                't' => 'ｔ',
                'u' => 'ｕ',
                'v' => 'ｖ',
                'w' => 'ｗ',
                'x' => 'ｘ',
                'y' => 'ｙ',
                'z' => 'ｚ',
                'A' => 'Ａ',
                'B' => 'Ｂ',
                'C' => 'Ｃ',
                'D' => 'Ｄ',
                'E' => 'Ｅ',
                'F' => 'Ｆ',
                'G' => 'Ｇ',
                'H' => 'Ｈ',
                'I' => 'Ｉ',
                'J' => 'Ｊ',
                'K' => 'Ｋ',
                'L' => 'Ｌ',
                'M' => 'Ｍ',
                'N' => 'Ｎ',
                'O' => 'Ｏ',
                'P' => 'Ｐ',
                'Q' => 'Ｑ',
                'R' => 'Ｒ',
                'S' => 'Ｓ',
                'T' => 'Ｔ',
                'U' => 'Ｕ',
                'V' => 'Ｖ',
                'W' => 'Ｗ',
                'X' => 'Ｘ',
                'Y' => 'Ｙ',
                'Z' => 'Ｚ'
            ],
            'a' => [
                '０' => '0',
                '１' => '1',
                '２' => '2',
                '３' => '3',
                '４' => '4',
                '５' => '5',
                '６' => '6',
                '７' => '7',
                '８' => '8',
                '９' => '9',
                'ａ' => 'a',
                'ｂ' => 'b',
                'ｃ' => 'c',
                'ｄ' => 'd',
                'ｅ' => 'e',
                'ｆ' => 'f',
                'ｇ' => 'g',
                'ｈ' => 'h',
                'ｉ' => 'i',
                'ｊ' => 'j',
                'ｋ' => 'k',
                'ｌ' => 'l',
                'ｍ' => 'm',
                'ｎ' => 'n',
                'ｏ' => 'o',
                'ｐ' => 'p',
                'ｑ' => 'q',
                'ｒ' => 'r',
                'ｓ' => 's',
                'ｔ' => 't',
                'ｕ' => 'u',
                'ｖ' => 'v',
                'ｗ' => 'w',
                'ｘ' => 'x',
                'ｙ' => 'y',
                'ｚ' => 'z',
                'Ａ' => 'A',
                'Ｂ' => 'B',
                'Ｃ' => 'C',
                'Ｄ' => 'D',
                'Ｅ' => 'E',
                'Ｆ' => 'F',
                'Ｇ' => 'G',
                'Ｈ' => 'H',
                'Ｉ' => 'I',
                'Ｊ' => 'J',
                'Ｋ' => 'K',
                'Ｌ' => 'L',
                'Ｍ' => 'M',
                'Ｎ' => 'N',
                'Ｏ' => 'O',
                'Ｐ' => 'P',
                'Ｑ' => 'Q',
                'Ｒ' => 'R',
                'Ｓ' => 'S',
                'Ｔ' => 'T',
                'Ｕ' => 'U',
                'Ｖ' => 'V',
                'Ｗ' => 'W',
                'Ｘ' => 'X',
                'Ｙ' => 'Y',
                'Ｚ' => 'Z'
            ],
            'K' => ['ｱ' => 'ア', 'ｲ' => 'イ', 'ｳ' => 'ウ', 'ｴ' => 'エ', 'ｵ' => 'オ'],
            'k' => ['ア' => 'ｱ', 'イ' => 'ｲ', 'ウ' => 'ｳ', 'エ' => 'ｴ', 'オ' => 'ｵ'],
        ];

        $modes = str_split($mode);
        foreach ($modes as $m) {
            if (isset($map[$m])) {
                $string = strtr($string, $map[$m]);
            }
        }

        return $string;
    }

    /**
     * Parses GET/POST/COOKIE data and sets global variables.
     *
     * This is a polyfill for the mb_parse_str() function. It parses the query string
     * into the provided result array. This implementation uses PHP's native parse_str()
     * function, which handles URL-encoded strings.
     *
     * @see https://www.php.net/manual/en/function.mb-parse-str.php
     *
     * @param string $string The URL-encoded query string to parse
     * @param array $result The array to store the parsed results
     * @return bool Returns true on success
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_parse_str(string $string, array &$result): bool
    {
        parse_str($string, $result);

        return true;
    }

    /**
     * Gets the preferred MIME name for a given encoding.
     *
     * This polyfill returns the preferred MIME name for a character encoding.
     * It supports common character encodings and their MIME names.
     *
     * @see https://www.php.net/manual/en/function.mb-preferred-mime-name.php
     *
     * @param string $encoding The character encoding to look up
     * @return string|false The preferred MIME name, or false if not found
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_preferred_mime_name(string $encoding): string|false
    {
        $encoding = strtoupper($encoding);
        $map = [
            'UTF-8' => 'UTF-8',
            'UTF8' => 'UTF-8',
            'ISO-8859-1' => 'ISO-8859-1',
            'ISO-8859-2' => 'ISO-8859-2',
            'ISO-8859-3' => 'ISO-8859-3',
            'ISO-8859-4' => 'ISO-8859-4',
            'ISO-8859-5' => 'ISO-8859-5',
            'ISO-8859-6' => 'ISO-8859-6',
            'ISO-8859-7' => 'ISO-8859-7',
            'ISO-8859-8' => 'ISO-8859-8',
            'ISO-8859-9' => 'ISO-8859-9',
            'ISO-8859-10' => 'ISO-8859-10',
            'ISO-8859-13' => 'ISO-8859-13',
            'ISO-8859-14' => 'ISO-8859-14',
            'ISO-8859-15' => 'ISO-8859-15',
            'SJIS' => 'Shift_JIS',
            'SHIFT_JIS' => 'Shift_JIS',
            'EUC-JP' => 'EUC-JP',
            'WINDOWS-1251' => 'windows-1251',
            'WINDOWS-1252' => 'windows-1252',
            'KOI8-R' => 'KOI8-R',
            'BIG5' => 'Big5',
            'GB2312' => 'GB2312',
            'US-ASCII' => 'US-ASCII',
        ];

        if (!isset($map[$encoding])) {
            throw new \InvalidArgumentException('mb_preferred_mime_name(): Argument #1 ($encoding) must be a valid encoding, "' . $encoding . '" given');
        }

        return $map[$encoding] ?? false;
    }

    /**
     * Gets or sets the encoding for multibyte regex.
     *
     * This polyfill manages the character encoding used for multibyte regular
     * expression functions. The default encoding is UTF-8.
     *
     * @see https://www.php.net/manual/en/function.mb-regex-encoding.php
     *
     * @param string|null $encoding The encoding to set, or null to get current encoding
     * @return string|bool Returns the encoding on get, true on set
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_regex_encoding(?string $encoding = null): string|bool
    {
        if (null === $encoding) {
            return self::$regexEncoding;
        }

        self::$regexEncoding = $encoding;

        return true;
    }

    /**
     * Gets or sets the options for multibyte regex.
     *
     * This polyfill manages the options used for multibyte regular expression
     * functions. The default options are 'kr' (Korean locale support).
     *
     * @see https://www.php.net/manual/en/function.mb-regex-set-options.php
     *
     * @param string|null $options The options to set, or null to get current options
     * @return string Returns the previous options
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_regex_set_options(?string $options = null): string
    {
        if (null === $options) {
            return self::$regexOptions;
        }

        $prev = self::$regexOptions;
        self::$regexOptions = $options;

        return $prev;
    }

    /**
     * Sends email with proper encoding for multibyte characters.
     *
     * This polyfill for mb_send_mail() handles encoding of email headers and body
     * for messages containing multibyte characters. It encodes the subject using
     * base64 encoding and sets appropriate Content-Type headers.
     *
     * @see https://www.php.net/manual/en/function.mb-send-mail.php
     *
     * @param string $to The recipient email address
     * @param string $subject The email subject
     * @param string $message The email message body
     * @param array|string $additional_headers Additional headers (string or array)
     * @param string|null $additional_params Additional parameters to pass to mail()
     * @return bool Returns true if the mail was accepted for delivery, false otherwise
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_send_mail(string $to, string $subject, string $message, array|string $additional_headers = [], ?string $additional_params = null): bool
    {
        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $headers = [];
        if (is_string($additional_headers) && $additional_headers !== '') {
            $headers = explode("\r\n", $additional_headers);
        } elseif (is_array($additional_headers)) {
            $headers = $additional_headers;
        }

        $hasContentType = false;
        foreach ($headers as $header) {
            $headerLine = is_array($header) ? ($header[0] ?? '') : $header;
            if (stripos((string) $headerLine, 'Content-Type:') === 0) {
                $hasContentType = true;
                break;
            }
        }

        if (!$hasContentType) {
            if (is_array($additional_headers)) {
                $headers['Content-Type'] = 'text/plain; charset=UTF-8';
                $headers['Content-Transfer-Encoding'] = 'base64';
            } else {
                $headers[] = 'Content-Type: text/plain; charset=UTF-8';
                $headers[] = 'Content-Transfer-Encoding: base64';
            }
            $message = chunk_split(base64_encode($message));
        }

        $headerStr = '';
        if (is_array($additional_headers)) {
            foreach ($headers as $k => $v) {
                if (is_int($k)) {
                    $headerStr .= $v . "\r\n";
                } else {
                    if (is_array($v)) {
                        foreach ($v as $subV) {
                            $headerStr .= "$k: $subV\r\n";
                        }
                    } else {
                        $headerStr .= "$k: $v\r\n";
                    }
                }
            }
        } else {
            $headerStr = implode("\r\n", $headers);
        }
        $headerStr = trim($headerStr);

        if ($additional_params !== null) {
            return mail($to, $subject, $message, $headerStr, $additional_params);
        }

        return mail($to, $subject, $message, $headerStr);
    }

    /**
     * Splits a multibyte string using a regular expression.
     *
     * This polyfill for mb_split() splits a string into an array using a
     * multibyte-safe regular expression as the delimiter. It automatically
     * selects an appropriate regex delimiter and adds the 'u' modifier for
     * UTF-8 support.
     *
     * @see https://www.php.net/manual/en/function.mb-split.php
     *
     * @param string $pattern The regular expression pattern
     * @param string $string The string to split
     * @param int $limit The maximum number of elements to return (-1 for no limit)
     * @return array|false An array of substrings, or false on failure
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_split(string $pattern, string $string, int $limit = -1): array|false
    {
        $delimiter = '#';
        if (str_contains($pattern, '#')) {
            $delimiter = '~';
            if (str_contains($pattern, '~')) {
                $delimiter = '/';
            }
        }
        $regex = $delimiter . $pattern . $delimiter . 'u';

        return preg_split($regex, $string, $limit);
    }

    /**
     * Gets a portion of a multibyte string while maintaining multibyte safety.
     *
     * This polyfill for mb_strcut() extracts a substring from a multibyte string
     * similar to substr(), but operates on bytes rather than characters while
     * maintaining character boundary integrity for UTF-8 encoded strings.
     *
     * The function ensures that the cut doesn't occur in the middle of a
     * multibyte character by adjusting the start position backwards if needed.
     *
     * @see https://www.php.net/manual/en/function.mb-strcut.php
     *
     * @param string $string The input string
     * @param int $start The starting position in bytes
     * @param int|null $length The length in bytes (null for rest of string)
     * @param string|null $encoding The character encoding (defaults to UTF-8 or mb_internal_encoding())
     * @return string The extracted portion of the string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_strcut(string $string, int $start, ?int $length = null, ?string $encoding = null): string
    {
        $encoding = $encoding ?? (function_exists('mb_internal_encoding') ? mb_internal_encoding() : 'UTF-8');
        $strlen = strlen($string);

        if ($start < 0) {
            $start = max(0, $strlen + $start);
        }

        if ($start >= $strlen) {
            return '';
        }

        if ($length === null) {
            $length = $strlen - $start;
        } elseif ($length < 0) {
            $length = max(0, ($strlen + $length) - $start);
        }

        $length = min($length, $strlen - $start);
        if ($length <= 0) {
            return '';
        }

        if (strtoupper($encoding) === 'UTF-8' || strtoupper($encoding) === 'UTF8') {
            while ($start > 0 && (ord($string[$start]) >= 0x80 && ord($string[$start]) <= 0xBF)) {
                $start--;
            }
        }

        $cut = substr($string, $start, $length);

        $checkEncoding = function_exists('mb_check_encoding')
            ? fn ($c) => mb_check_encoding($c, $encoding)
            : fn ($c) => (bool) preg_match('//u', $c);

        while ($length > 0 && !$checkEncoding($cut)) {
            $length--;
            $cut = substr($string, $start, $length);
        }

        return $cut;
    }

    /**
     * Truncates a multibyte string to a specified width.
     *
     * This polyfill for mb_strimwidth() truncates a string to a given width,
     * adding a trim marker if the string exceeds the specified width. The width
     * is calculated based on character width: ASCII characters count as 1,
     * multibyte characters count as 2.
     *
     * @see https://www.php.net/manual/en/function.mb-strimwidth.php
     *
     * @param string $string The string to truncate
     * @param int $start The starting position
     * @param int $width The desired width in characters
     * @param string $trim_marker The string to append when truncating (default: '')
     * @param string|null $encoding The character encoding (not used in this polyfill)
     * @return string The truncated string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
    {
        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $chars = array_slice($chars, $start);

        $totalWidth = 0;
        $charWidths = [];
        foreach ($chars as $char) {
            $w = (strlen($char) === 1 && ord($char) < 128) ? 1 : 2;
            $charWidths[] = $w;
            $totalWidth += $w;
        }

        if ($totalWidth <= $width) {
            return implode('', $chars);
        }

        $markerWidth = 0;
        if ($trim_marker !== '') {
            $markerChars = preg_split('//u', $trim_marker, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($markerChars as $c) {
                $markerWidth += (strlen($c) === 1 && ord($c) < 128) ? 1 : 2;
            }
        }

        $result = '';
        $currentWidth = 0;
        foreach ($chars as $i => $char) {
            $w = $charWidths[$i];
            if ($currentWidth + $w + $markerWidth > $width) {
                $result .= $trim_marker;
                break;
            }
            $result .= $char;
            $currentWidth += $w;
        }

        return $result;
    }
}
