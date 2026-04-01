<?php

declare(strict_types=1);

namespace Phuture\Continuum\Extension;

use InvalidArgumentException;

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
        $encoding = $encoding ?? (function_exists('mb_internal_encoding') ? mb_internal_encoding() : 'UTF-8');

        // Mapping tables for conversion
        $map = [
            'R' => [ // Han-kaku Alphabets -> Zen-kaku Alphabets
                'a' => 'ａ', 'b' => 'ｂ', 'c' => 'ｃ', 'd' => 'ｄ', 'e' => 'ｅ',
                'f' => 'ｆ', 'g' => 'ｇ', 'h' => 'ｈ', 'i' => 'ｉ', 'j' => 'ｊ',
                'k' => 'ｋ', 'l' => 'ｌ', 'm' => 'ｍ', 'n' => 'ｎ', 'o' => 'ｏ',
                'p' => 'ｐ', 'q' => 'ｑ', 'r' => 'ｒ', 's' => 'ｓ', 't' => 'ｔ',
                'u' => 'ｕ', 'v' => 'ｖ', 'w' => 'ｗ', 'x' => 'ｘ', 'y' => 'ｙ',
                'z' => 'ｚ', 'A' => 'Ａ', 'B' => 'Ｂ', 'C' => 'Ｃ', 'D' => 'Ｄ',
                'E' => 'Ｅ', 'F' => 'Ｆ', 'G' => 'Ｇ', 'H' => 'Ｈ', 'I' => 'Ｉ',
                'J' => 'Ｊ', 'K' => 'Ｋ', 'L' => 'Ｌ', 'M' => 'Ｍ', 'N' => 'Ｎ',
                'O' => 'Ｏ', 'P' => 'Ｐ', 'Q' => 'Ｑ', 'R' => 'Ｒ', 'S' => 'Ｓ',
                'T' => 'Ｔ', 'U' => 'Ｕ', 'V' => 'Ｖ', 'W' => 'Ｗ', 'X' => 'Ｘ',
                'Y' => 'Ｙ', 'Z' => 'Ｚ'
            ],
            'r' => [ // Zen-kaku Alphabets -> Han-kaku Alphabets
                'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e',
                'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j',
                'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o',
                'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't',
                'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y',
                'ｚ' => 'z', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D',
                'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I',
                'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N',
                'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S',
                'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X',
                'Ｙ' => 'Y', 'Ｚ' => 'Z'
            ],
            'N' => [ // Han-kaku Numbers -> Zen-kaku Numbers
                '0' => '０', '1' => '１', '2' => '２', '3' => '３', '4' => '４',
                '5' => '５', '6' => '６', '7' => '７', '8' => '８', '9' => '９'
            ],
            'n' => [ // Zen-kaku Numbers -> Han-kaku Numbers
                '０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
                '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9'
            ],
            'K' => [ // Han-kaku Katakana -> Zen-kaku Katakana
                'ｱ' => 'ア', 'ｲ' => 'イ', 'ｳ' => 'ウ', 'ｴ' => 'エ', 'ｵ' => 'オ',
                'ｶ' => 'カ', 'ｷ' => 'キ', 'ｸ' => 'ク', 'ｹ' => 'ケ', 'ｺ' => 'コ',
                'ｻ' => 'サ', 'ｼ' => 'シ', 'ｽ' => 'ス', 'ｾ' => 'セ', 'ｿ' => 'ソ',
                'ﾀ' => 'タ', 'ﾁ' => 'チ', 'ﾂ' => 'ツ', 'ﾃ' => 'テ', 'ﾄ' => 'ト',
                'ﾅ' => 'ナ', 'ﾆ' => 'ニ', 'ﾇ' => 'ヌ', 'ﾈ' => 'ネ', 'ﾉ' => 'ノ',
                'ﾊ' => 'ハ', 'ﾋ' => 'ヒ', 'ﾌ' => 'フ', 'ﾍ' => 'ヘ', 'ﾎ' => 'ホ',
                'ﾏ' => 'マ', 'ﾐ' => 'ミ', 'ﾑ' => 'ム', 'ﾒ' => 'メ', 'ﾓ' => 'モ',
                'ﾔ' => 'ヤ', 'ﾕ' => 'ユ', 'ﾖ' => 'ヨ',
                'ﾗ' => 'ラ', 'ﾘ' => 'リ', 'ﾙ' => 'ル', 'ﾚ' => 'レ', 'ﾛ' => 'ロ',
                'ﾜ' => 'ワ', 'ｦ' => 'ヲ', 'ﾝ' => 'ン',
                'ｧ' => 'ァ', 'ｨ' => 'ィ', 'ｩ' => 'ゥ', 'ｪ' => 'ェ', 'ｫ' => 'ォ',
                'ｯ' => 'ッ', 'ｬ' => 'ャ', 'ｭ' => 'ュ', 'ｮ' => 'ョ',
                'ｰ' => 'ー', '､' => '、', '｡' => '。', '｢' => '「', '｣' => '」', '･' => '・'
            ],
            'k' => [ // Zen-kaku Katakana -> Han-kaku Katakana
                'ア' => 'ｱ', 'イ' => 'ｲ', 'ウ' => 'ｳ', 'エ' => 'ｴ', 'オ' => 'ｵ',
                'カ' => 'ｶ', 'キ' => 'ｷ', 'ク' => 'ｸ', 'ケ' => 'ｹ', 'コ' => 'ｺ',
                'サ' => 'ｻ', 'シ' => 'ｼ', 'ス' => 'ｽ', 'セ' => 'ｾ', 'ソ' => 'ｿ',
                'タ' => 'ﾀ', 'チ' => 'ﾁ', 'ツ' => 'ﾂ', 'テ' => 'ﾃ', 'ト' => 'ﾄ',
                'ナ' => 'ﾅ', 'ニ' => 'ﾆ', 'ヌ' => 'ﾇ', 'ネ' => 'ﾈ', 'ノ' => 'ﾉ',
                'ハ' => 'ﾊ', 'ヒ' => 'ﾋ', 'フ' => 'ﾌ', 'ヘ' => 'ﾍ', 'ホ' => 'ﾎ',
                'マ' => 'ﾏ', 'ミ' => 'ﾐ', 'ム' => 'ﾑ', 'メ' => 'ﾒ', 'モ' => 'ﾓ',
                'ヤ' => 'ﾔ', 'ユ' => 'ﾕ', 'ヨ' => 'ﾖ',
                'ラ' => 'ﾗ', 'リ' => 'ﾘ', 'ル' => 'ﾙ', 'レ' => 'ﾚ', 'ロ' => 'ﾛ',
                'ワ' => 'ﾜ', 'ヲ' => 'ｦ', 'ン' => 'ﾝ',
                'ァ' => 'ｧ', 'ィ' => 'ｨ', 'ゥ' => 'ｩ', 'ェ' => 'ｪ', 'ォ' => 'ｫ',
                'ッ' => 'ｯ', 'ャ' => 'ｬ', 'ュ' => 'ｭ', 'ョ' => 'ｮ',
                'ー' => 'ｰ', '、' => '､', '।' => '｡', '「' => '｢', '」' => '｣', '・' => '･',
                // Voiced Katakana (Dakuten)
                'ガ' => 'ｶﾞ', 'ギ' => 'ｷﾞ', 'グ' => 'ｸﾞ', 'ゲ' => 'ｹﾞ', 'ゴ' => 'ｺﾞ',
                'ザ' => 'ｻﾞ', 'ジ' => 'ｼﾞ', 'ズ' => 'ｽﾞ', 'ゼ' => 'ｾﾞ', 'ゾ' => 'ｿﾞ',
                'ダ' => 'ﾀﾞ', 'ヂ' => 'ﾁﾞ', 'ヅ' => 'ﾂﾞ', 'デ' => 'ﾃﾞ', 'ド' => 'ﾄﾞ',
                'バ' => 'ﾊﾞ', 'ビ' => 'ﾋﾞ', 'ブ' => 'ﾌﾞ', 'ベ' => 'ﾍﾞ', 'ボ' => 'ﾎﾞ',
                'ヴ' => 'ｳﾞ',
                // Semi-voiced Katakana (Handakuten)
                'パ' => 'ﾊﾟ', 'ピ' => 'ﾋﾟ', 'プ' => 'ﾌﾟ', 'ペ' => 'ﾍﾟ', 'ポ' => 'ﾎﾟ'
            ],
            'V' => [ // Collapse voiced sound marks
                'ｶﾞ' => 'ガ', 'ｷﾞ' => 'ギ', 'ｸﾞ' => 'グ', 'ｹﾞ' => 'ゲ', 'ｺﾞ' => 'ゴ',
                'ｻﾞ' => 'ザ', 'ｼﾞ' => 'ジ', 'ｽﾞ' => 'ズ', 'ｾﾞ' => 'ゼ', 'ｿﾞ' => 'ゾ',
                'ﾀﾞ' => 'ダ', 'ﾁﾞ' => 'ヂ', 'ﾂﾞ' => 'ヅ', 'ﾃﾞ' => 'デ', 'ﾄﾞ' => 'ド',
                'ﾊﾞ' => 'バ', 'ﾋﾞ' => 'ビ', 'ﾌﾞ' => 'ブ', 'ﾍﾞ' => 'ベ', 'ﾎﾞ' => 'ボ',
                'ﾊﾟ' => 'パ', 'ﾋﾟ' => 'ピ', 'ﾌﾟ' => 'プ', 'ﾍﾟ' => 'ペ', 'ﾎﾟ' => 'ポ',
                'ｳﾞ' => 'ヴ'
            ]
        ];

        // Ensure we are working with UTF-8 internally
        $originalEncoding = $encoding;
        if (strtoupper($encoding) !== 'UTF-8' && strtoupper($encoding) !== 'UTF8') {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding);
        }

        // Apply voiced mark collapse if 'V' is present and we are converting to Zen-kaku
        if (strpos($mode, 'V') !== false) {
            $string = strtr($string, $map['V']);
        }

        // Handle Alphanumeric options 'A' (r+n), 'a' (R+N)
        $effectiveMode = str_replace(['A', 'a'], ['RN', 'rn'], $mode);
        $modes = str_split($effectiveMode);

        foreach ($modes as $m) {
            if (isset($map[$m])) {
                $string = strtr($string, $map[$m]);
            }
        }

        // Convert back to original encoding if needed
        if (strtoupper($originalEncoding) !== 'UTF-8' && strtoupper($originalEncoding) !== 'UTF8') {
            $string = mb_convert_encoding($string, $originalEncoding, 'UTF-8');
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
            throw new InvalidArgumentException('mb_preferred_mime_name(): Argument #1 ($encoding) must be a valid encoding, "' . $encoding . '" given');
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
        $encoding = function_exists('mb_internal_encoding') ? mb_internal_encoding() : 'UTF-8';
        $language = function_exists('mb_language') ? mb_language() : 'neutral';

        // Determine default charset based on language
        $charset = 'UTF-8';
        $transferEncoding = 'base64';

        if ($language === 'Japanese') {
            $charset = 'ISO-2022-JP';
            $transferEncoding = '7bit';
        }

        // Encode subject
        $subject = '=?' . $charset . '?B?' . base64_encode(mb_convert_encoding($subject, $charset, $encoding)) . '?=';

        // Prepare headers
        $headers = [];
        if (is_string($additional_headers) && $additional_headers !== '') {
            $headers = explode("\r\n", $additional_headers);
        } elseif (is_array($additional_headers)) {
            $headers = $additional_headers;
        }

        $hasContentType = false;
        foreach ($headers as $k => $v) {
            $headerLine = is_int($k) ? $v : $k;
            if (stripos((string)$headerLine, 'Content-Type:') === 0) {
                $hasContentType = true;
                break;
            }
        }

        if (!$hasContentType) {
            $contentType = "text/plain; charset=$charset";
            if (is_array($additional_headers)) {
                $headers['Content-Type'] = $contentType;
                $headers['Content-Transfer-Encoding'] = $transferEncoding;
            } else {
                $headers[] = "Content-Type: $contentType";
                $headers[] = "Content-Transfer-Encoding: $transferEncoding";
            }

            // Encode message
            $message = mb_convert_encoding($message, $charset, $encoding);
            if ($transferEncoding === 'base64') {
                $message = chunk_split(base64_encode($message));
            }
        }

        $headerStr = '';
        if (is_array($additional_headers)) {
            foreach ($headers as $k => $v) {
                if (is_int($k)) {
                    $headerStr .= $v . "\r\n";
                } else {
                    $headerStr .= "$k: $v\r\n";
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
        $encoding = $encoding ?? (function_exists('mb_internal_encoding') ? mb_internal_encoding() : 'UTF-8');

        if (strtoupper($encoding) !== 'UTF-8' && strtoupper($encoding) !== 'UTF8') {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding);
            $trim_marker = mb_convert_encoding($trim_marker, 'UTF-8', $encoding);
        }

        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $totalChars = count($chars);

        if ($start < 0) {
            $start = max(0, $totalChars + $start);
        }

        $chars = array_slice($chars, $start);

        $currentWidth = 0;
        $charWidths = [];
        foreach ($chars as $char) {
            $w = self::getCharWidth($char);
            $charWidths[] = $w;
            $currentWidth += $w;
        }

        if ($currentWidth <= $width) {
            $result = implode('', $chars);

            return strtoupper($encoding) !== 'UTF-8' && strtoupper($encoding) !== 'UTF8'
                ? mb_convert_encoding($result, $encoding, 'UTF-8')
                : $result;
        }

        $markerWidth = 0;
        $markerChars = preg_split('//u', $trim_marker, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($markerChars as $mc) {
            $markerWidth += self::getCharWidth($mc);
        }

        $targetWidth = $width - $markerWidth;
        $resultChars = [];
        $resultWidth = 0;

        foreach ($chars as $i => $char) {
            $w = $charWidths[$i];
            if ($resultWidth + $w > $targetWidth) {
                break;
            }
            $resultChars[] = $char;
            $resultWidth += $w;
        }

        $result = implode('', $resultChars) . $trim_marker;

        return strtoupper($encoding) !== 'UTF-8' && strtoupper($encoding) !== 'UTF8'
            ? mb_convert_encoding($result, $encoding, 'UTF-8')
            : $result;
    }

    /**
     * Calculates the width of a single UTF-8 character based on East Asian Width.
     *
     * @param string $char The UTF-8 character
     * @return int The width (1 or 2)
     */
    private static function getCharWidth(string $char): int
    {
        $cp = self::getUnicodeCodePoint($char);

        // East Asian Width Fullwidth/Wide ranges
        if (
            ($cp >= 0x1100 && $cp <= 0x115F) || ($cp >= 0x11A3 && $cp <= 0x11A7) ||
            ($cp >= 0x11FA && $cp <= 0x11FF) || ($cp >= 0x2329 && $cp <= 0x232A) ||
            ($cp >= 0x2E80 && $cp <= 0x2E99) || ($cp >= 0x2E9B && $cp <= 0x2EF3) ||
            ($cp >= 0x2F00 && $cp <= 0x2FD5) || ($cp >= 0x2FF0 && $cp <= 0x2FFB) ||
            ($cp >= 0x3000 && $cp <= 0x303E) || ($cp >= 0x3041 && $cp <= 0x3096) ||
            ($cp >= 0x3099 && $cp <= 0x30FF) || ($cp >= 0x3105 && $cp <= 0x312D) ||
            ($cp >= 0x3131 && $cp <= 0x318E) || ($cp >= 0x3190 && $cp <= 0x31BA) ||
            ($cp >= 0x31C0 && $cp <= 0x31E3) || ($cp >= 0x31F0 && $cp <= 0x321E) ||
            ($cp >= 0x3220 && $cp <= 0x3247) || ($cp >= 0x3250 && $cp <= 0x32FE) ||
            ($cp >= 0x3300 && $cp <= 0x4DBF) || ($cp >= 0x4E00 && $cp <= 0xA48C) ||
            ($cp >= 0xA490 && $cp <= 0xA4C6) || ($cp >= 0xA960 && $cp <= 0xA97C) ||
            ($cp >= 0xAC00 && $cp <= 0xD7A3) || ($cp >= 0xD7B0 && $cp <= 0xD7C6) ||
            ($cp >= 0xD7CB && $cp <= 0xD7FB) || ($cp >= 0xF900 && $cp <= 0xFAFF) ||
            ($cp >= 0xFE10 && $cp <= 0xFE19) || ($cp >= 0xFE30 && $cp <= 0xFE52) ||
            ($cp >= 0xFE54 && $cp <= 0xFE66) || ($cp >= 0xFE68 && $cp <= 0xFE6B) ||
            ($cp >= 0xFF01 && $cp <= 0xFF60) || ($cp >= 0xFFE0 && $cp <= 0xFFE6) ||
            ($cp >= 0x1B000 && $cp <= 0x1B001) || ($cp >= 0x1F200 && $cp <= 0x1F202) ||
            ($cp >= 0x1F210 && $cp <= 0x1F23A) || ($cp >= 0x1F240 && $cp <= 0x1F248) ||
            ($cp >= 0x1F250 && $cp <= 0x1F251) || ($cp >= 0x20000 && $cp <= 0x2FFFD) ||
            ($cp >= 0x30000 && $cp <= 0x3FFFD) ||
            // Common emoji ranges that are treated as width 2 in PHP
            ($cp >= 0x1F300 && $cp <= 0x1F6FF) || ($cp >= 0x1F900 && $cp <= 0x1F9FF) ||
            ($cp >= 0x1F000 && $cp <= 0x1F02F) || ($cp >= 0x1F0A0 && $cp <= 0x1F0FF) ||
            ($cp >= 0x2600 && $cp <= 0x26FF) || ($cp >= 0x2700 && $cp <= 0x27BF)
        ) {
            return 2;
        }

        return 1;
    }

    /**
     * Gets the Unicode code point of a UTF-8 character.
     *
     * @param string $char The UTF-8 character
     * @return int The code point
     */
    private static function getUnicodeCodePoint(string $char): int
    {
        $ord0 = ord($char[0]);
        if ($ord0 <= 0x7F) {
            return $ord0;
        }

        if ($ord0 >= 0xC2 && $ord0 <= 0xDF) {
            return (($ord0 & 0x1F) << 6) | (ord($char[1]) & 0x3F);
        }

        if ($ord0 >= 0xE0 && $ord0 <= 0xEF) {
            return (($ord0 & 0x0F) << 12) | ((ord($char[1]) & 0x3F) << 6) | (ord($char[2]) & 0x3F);
        }

        if ($ord0 >= 0xF0 && $ord0 <= 0xF4) {
            return (($ord0 & 0x07) << 18) | ((ord($char[1]) & 0x3F) << 12) | ((ord($char[2]) & 0x3F) << 6) | (ord($char[3]) & 0x3F);
        }

        return 0;
    }
}
