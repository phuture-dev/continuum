<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Throwable;
use Tester\{Assert, TestCase};
use Phuture\Continuum\Extension\Mbstring;

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Mbstring polyfill against native mbstring functions.
 *
 * @testCase
 */
class MbstringParityTest extends TestCase
{
    private bool $hasNativeMbstring;

    protected function setUp(): void
    {
        $this->hasNativeMbstring = extension_loaded('mbstring');
    }

    // =========================================================================
    // mb_parse_str Parity Tests
    // =========================================================================

    public function testMbParseStrParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_parse_str() not available');

            return;
        }

        $testCases = [
            'foo=bar&baz=qux',
            'name=John%20Doe&age=30',
            'a=1&b=2&c=3',
            '',
        ];

        foreach ($testCases as $queryString) {
            $nativeResult = [];
            $polyfillResult = [];

            mb_parse_str($queryString, $nativeResult);
            Mbstring::mb_parse_str($queryString, $polyfillResult);

            Assert::same($nativeResult, $polyfillResult, "Parity failed for mb_parse_str('{$queryString}')");
        }
    }

    public function testMbParseStrMultibyteParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_parse_str() not available');

            return;
        }

        $testCases = [
            'name=%E3%81%82&city=%E6%9D%B1%E4%BA%AC',
            'title=%E3%83%86%E3%82%B9%E3%83%88',
        ];

        foreach ($testCases as $queryString) {
            $nativeResult = [];
            $polyfillResult = [];

            mb_parse_str($queryString, $nativeResult);
            Mbstring::mb_parse_str($queryString, $polyfillResult);

            Assert::same($nativeResult, $polyfillResult, "Parity failed for multibyte query string");
        }
    }

    // =========================================================================
    // mb_preferred_mime_name Parity Tests
    // =========================================================================

    public function testMbPreferredMimeNameParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_preferred_mime_name() not available');

            return;
        }

        $testCases = [
            'UTF-8',
            'utf-8',
            'UTF8',
            'ISO-8859-1',
            'SJIS',
            'Shift_JIS',
            'EUC-JP',
        ];

        foreach ($testCases as $encoding) {
            $native = mb_preferred_mime_name($encoding);
            $polyfill = Mbstring::mb_preferred_mime_name($encoding);

            Assert::same($native, $polyfill, "Parity failed for mb_preferred_mime_name('{$encoding}')");
        }
    }

    public function testMbPreferredMimeNameUnknownParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_preferred_mime_name() not available');

            return;
        }

        $testCases = [
            'UNKNOWN-ENCODING',
            'INVALID-CODE',
        ];

        foreach ($testCases as $encoding) {
            // Both native and polyfill should throw exceptions for unknown encodings
            $nativeThrew = false;
            $polyfillThrew = false;

            try {
                mb_preferred_mime_name($encoding);
            } catch (Throwable $e) {
                $nativeThrew = true;
            }

            try {
                Mbstring::mb_preferred_mime_name($encoding);
            } catch (Throwable $e) {
                $polyfillThrew = true;
            }

            Assert::true($nativeThrew && $polyfillThrew, "Both native and polyfill should throw exception for unknown encoding '{$encoding}'");
        }
    }

    // =========================================================================
    // mb_split Parity Tests
    // =========================================================================

    public function testMbSplitParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_split() not available');

            return;
        }

        $testCases = [
            ['[,\s]+', 'hello, world test'],
            ['[|]', 'foo|bar|baz'],
            ['-', 'a-b-c-d'],
        ];

        foreach ($testCases as [$pattern, $string]) {
            $native = mb_split($pattern, $string);
            $polyfill = Mbstring::mb_split($pattern, $string);

            Assert::same($native, $polyfill, "Parity failed for mb_split('{$pattern}', '{$string}')");
        }
    }

    public function testMbSplitWithLimitParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_split() not available');

            return;
        }

        $testCases = [
            ['[,\s]+', 'hello, world test', 2],
            ['[|]', 'foo|bar|baz', 3],
        ];

        foreach ($testCases as [$pattern, $string, $limit]) {
            $native = mb_split($pattern, $string, $limit);
            $polyfill = Mbstring::mb_split($pattern, $string, $limit);

            Assert::same($native, $polyfill, "Parity failed for mb_split('{$pattern}', '{$string}', {$limit})");
        }
    }

    // =========================================================================
    // mb_strcut Parity Tests
    // =========================================================================

    public function testMbStrcutParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_strcut() not available');

            return;
        }

        $testCases = [
            ['hello world', 0, 5],
            ['hello world', 6, 5],
            ['test string', 0, 4],
        ];

        foreach ($testCases as [$string, $start, $length]) {
            $native = mb_strcut($string, $start, $length);
            $polyfill = Mbstring::mb_strcut($string, $start, $length);

            Assert::same($native, $polyfill, "Parity failed for mb_strcut('{$string}', {$start}, {$length})");
        }
    }

    public function testMbStrcutUtf8Parity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_strcut() not available');

            return;
        }

        $testCases = [
            ['こんにちは', 0, 3],
            ['こんにちは', 0, 6],
            ['あいうえお', 3, 6],
        ];

        foreach ($testCases as [$string, $start, $length]) {
            $native = mb_strcut($string, $start, $length, 'UTF-8');
            $polyfill = Mbstring::mb_strcut($string, $start, $length, 'UTF-8');

            Assert::same($native, $polyfill, "Parity failed for UTF-8 string");
        }
    }

    // =========================================================================
    // mb_strimwidth Parity Tests
    // =========================================================================

    public function testMbStrimwidthParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_strimwidth() not available');

            return;
        }

        $testCases = [
            ['hello world', 0, 5, ''],
            ['hello world', 0, 7, ''],
            ['test string', 0, 10, ''],
        ];

        foreach ($testCases as [$string, $start, $width, $marker]) {
            $native = mb_strimwidth($string, $start, $width, $marker);
            $polyfill = Mbstring::mb_strimwidth($string, $start, $width, $marker);

            Assert::same($native, $polyfill, "Parity failed for mb_strimwidth('{$string}', {$start}, {$width})");
        }
    }

    public function testMbStrimwidthWithMarkerParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_strimwidth() not available');

            return;
        }

        $testCases = [
            ['hello world', 0, 5, '...'],
            ['hello world', 0, 8, '>>'],
        ];

        foreach ($testCases as [$string, $start, $width, $marker]) {
            $native = mb_strimwidth($string, $start, $width, $marker);
            $polyfill = Mbstring::mb_strimwidth($string, $start, $width, $marker);

            Assert::same($native, $polyfill, "Parity failed for mb_strimwidth with marker '{$marker}'");
        }
    }

    public function testMbStrimwidthMultibyteParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_strimwidth() not available');

            return;
        }

        $testCases = [
            ['こんにちは世界', 0, 10, '...'],
            ['あいうえお', 0, 5, '...'],
            ['ABCこんにちは', 0, 8, '...'],
        ];

        // Emojis often have different widths in older PHP versions (8.0)
        if (\PHP_VERSION_ID >= 80100) {
            $testCases[] = ['😀😁😂🤣', 0, 5, '...'];
        }

        foreach ($testCases as [$string, $start, $width, $marker]) {
            $native = mb_strimwidth($string, $start, $width, $marker, 'UTF-8');
            $polyfill = Mbstring::mb_strimwidth($string, $start, $width, $marker, 'UTF-8');

            Assert::same($native, $polyfill, "Parity failed for mb_strimwidth with multibyte string '{$string}'");
        }
    }

    // =========================================================================
    // mb_convert_kana Parity Tests
    // =========================================================================

    public function testMbConvertKanaParity(): void
    {
        if (!$this->hasNativeMbstring) {
            Assert::false($this->hasNativeMbstring, 'Native mb_convert_kana() not available');

            return;
        }

        $testCases = [
            ['0123456789', 'R'],
            ['０１２３４５６７８９', 'r'],
            ['abcdefg', 'R'],
            ['ａｂｃｄｅｆｇ', 'r'],
            ['ｱｲｳｴｵ', 'K'],
            ['アイウエオ', 'k'],
            ['ｶﾞｷﾞｸﾞｹﾞｺﾞ', 'KV'],
            ['ガギグゲゴ', 'k'],
            ['ﾊﾟﾋﾟﾌﾟﾍﾟﾎﾟ', 'KV'],
            ['パピプペポ', 'k'],
            ['ｳﾞ', 'KV'],
        ];

        foreach ($testCases as [$string, $mode]) {
            $native = mb_convert_kana($string, $mode, 'UTF-8');
            $polyfill = Mbstring::mb_convert_kana($string, $mode, 'UTF-8');

            Assert::same($native, $polyfill, "Parity failed for mb_convert_kana('{$string}', '{$mode}')");
        }
    }
}

(new MbstringParityTest())->run();
