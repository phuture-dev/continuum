<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use InvalidArgumentException;
use Tester\{Assert, TestCase};
use Phuture\Continuum\Extension\Mbstring;

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for Mbstring polyfill methods.
 *
 * @testCase
 */
class MbstringTest extends TestCase
{
    // =========================================================================
    // mb_convert_kana Tests
    // =========================================================================

    public function testMbConvertKanaBasic(): void
    {
        Assert::same('０１２３', Mbstring::mb_convert_kana('0123', 'A'));
        Assert::same('ａｂｃ', Mbstring::mb_convert_kana('abc', 'A'));
        Assert::same('ＡＢＣ', Mbstring::mb_convert_kana('ABC', 'A'));
    }

    public function testMbConvertKanaReverse(): void
    {
        Assert::same('0123', Mbstring::mb_convert_kana('０１２３', 'a'));
        Assert::same('abc', Mbstring::mb_convert_kana('ａｂｃ', 'a'));
        Assert::same('ABC', Mbstring::mb_convert_kana('ＡＢＣ', 'a'));
    }

    public function testMbConvertKanaKatakana(): void
    {
        Assert::same('ア', Mbstring::mb_convert_kana('ｱ', 'K'));
        Assert::same('ｱ', Mbstring::mb_convert_kana('ア', 'k'));
    }

    // =========================================================================
    // mb_preferred_mime_name Tests
    // =========================================================================

    public function testMbPreferredMimeName(): void
    {
        Assert::same('UTF-8', Mbstring::mb_preferred_mime_name('UTF-8'));
        Assert::same('Shift_JIS', Mbstring::mb_preferred_mime_name('SJIS'));

        // Test that unknown encoding throws ValueError
        Assert::exception(function () {
            Mbstring::mb_preferred_mime_name('UNKNOWN');
        }, InvalidArgumentException::class);
    }

    // =========================================================================
    // mb_split Tests
    // =========================================================================

    public function testMbSplit(): void
    {
        Assert::same(['hello', 'world', 'test'], Mbstring::mb_split('[\s,]+', 'hello, world test'));
        Assert::same(['hello', 'world', 'test'], Mbstring::mb_split('[,\s]+', 'hello, world test'));
    }

    // =========================================================================
    // mb_strcut Tests
    // =========================================================================

    public function testMbStrcut(): void
    {
        Assert::same('hello', Mbstring::mb_strcut('hello world', 0, 5));
        Assert::same('world', Mbstring::mb_strcut('hello world', 6, 5));
        Assert::same('こ', Mbstring::mb_strcut('こんにちは', 0, 3));
    }

    // =========================================================================
    // mb_strimwidth Tests
    // =========================================================================

    public function testMbStrimwidth(): void
    {
        Assert::same('hello', Mbstring::mb_strimwidth('hello world', 0, 5));
        Assert::same('he...', Mbstring::mb_strimwidth('hello world', 0, 5, '...'));
        Assert::same('こ', Mbstring::mb_strimwidth('こんにちは', 0, 2));
    }
}

(new MbstringTest())->run();
