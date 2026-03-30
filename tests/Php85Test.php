<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use CurlMultiHandle;
use Phuture\Continuum\Php85;
use RuntimeException;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for PHP 8.5 polyfill methods.
 *
 * @testCase
 */
class Php85Test extends TestCase
{
    // =========================================================================
    // Constants Tests
    // =========================================================================

    public function testPhpBuildDateConstant(): void
    {
        Assert::same('Dec 28 2025 00:00:00', Php85::PHP_BUILD_DATE);
    }

    public function testPhpBuildProviderConstant(): void
    {
        Assert::same('phuture/continuum', Php85::PHP_BUILD_PROVIDER);
    }

    // =========================================================================
    // grapheme_levenshtein Tests
    // =========================================================================

    public function testGraphemeLevenshteinIdenticalStrings(): void
    {
        Assert::same(0, Php85::grapheme_levenshtein('hello', 'hello'));
        Assert::same(0, Php85::grapheme_levenshtein('', ''));
        Assert::same(0, Php85::grapheme_levenshtein('cafe', 'cafe'));
    }

    public function testGraphemeLevenshteinEmptyStrings(): void
    {
        // Empty first string
        Assert::same(3, Php85::grapheme_levenshtein('', 'abc'));
        Assert::same(0, Php85::grapheme_levenshtein('', ''));

        // Empty second string
        Assert::same(3, Php85::grapheme_levenshtein('abc', ''));
    }

    public function testGraphemeLevenshteinBasicDistance(): void
    {
        // Single insertion
        Assert::same(1, Php85::grapheme_levenshtein('abc', 'abcd'));

        // Single deletion
        Assert::same(1, Php85::grapheme_levenshtein('abcd', 'abc'));

        // Single replacement
        Assert::same(1, Php85::grapheme_levenshtein('abc', 'axc'));

        // Multiple operations
        Assert::same(3, Php85::grapheme_levenshtein('kitten', 'sitting'));
        Assert::same(2, Php85::grapheme_levenshtein('book', 'back'));
    }

    public function testGraphemeLevenshteinCustomCosts(): void
    {
        // With custom costs: insert=2, replace=3, delete=2
        $result = Php85::grapheme_levenshtein('abc', 'axc', 2, 3, 2);
        Assert::same(3, $result); // 1 replacement * 3 = 3

        $result = Php85::grapheme_levenshtein('abc', 'abcd', 2, 3, 2);
        Assert::same(2, $result); // 1 insertion * 2 = 2

        $result = Php85::grapheme_levenshtein('abcd', 'abc', 2, 3, 2);
        Assert::same(2, $result); // 1 deletion * 2 = 2

        // Empty string with custom insertion cost
        $result = Php85::grapheme_levenshtein('', 'abc', 5, 1, 1);
        Assert::same(15, $result); // 3 insertions * 5 = 15

        // Empty string with custom deletion cost
        $result = Php85::grapheme_levenshtein('abc', '', 1, 1, 5);
        Assert::same(15, $result); // 3 deletions * 5 = 15
    }

    public function testGraphemeLevenshteinUnicodeStrings(): void
    {
        // UTF-8 strings with accented characters
        Assert::same(0, Php85::grapheme_levenshtein('café', 'café'));
        Assert::same(1, Php85::grapheme_levenshtein('café', 'cafe'));

        // Grapheme clusters - emoji with skin tone modifier
        // These are treated as single grapheme clusters
        $emoji1 = "👍🏻"; // Thumbs up + light skin tone (single grapheme)
        $emoji2 = "👍🏼"; // Thumbs up + medium-light skin tone (single grapheme)
        Assert::same(1, Php85::grapheme_levenshtein($emoji1, $emoji2));

        // Different grapheme clusters
        $emoji3 = "👨‍👩‍👧"; // Family emoji (zwj sequence, single grapheme)
        $emoji4 = "👨‍👩‍👦"; // Family emoji (zwj sequence, single grapheme)
        Assert::same(1, Php85::grapheme_levenshtein($emoji3, $emoji4));

        // Combining characters
        $nfc = "é";      // Precomposed é (U+00E9)
        $nfd = "e\u{0301}"; // e + combining acute accent
        Assert::same(0, Php85::grapheme_levenshtein($nfc, $nfd));

        // Multiple grapheme clusters - ï is one grapheme, i is one grapheme, so 1 replacement
        Assert::same(1, Php85::grapheme_levenshtein('naïve', 'naive'));
    }

    public function testGraphemeLevenshteinInvalidUtf8(): void
    {
        // Invalid UTF-8 in first string
        Assert::false(Php85::grapheme_levenshtein("\xFF\xFE", 'test'));

        // Invalid UTF-8 in second string
        Assert::false(Php85::grapheme_levenshtein('test', "\xFF\xFE"));

        // Both strings invalid
        Assert::false(Php85::grapheme_levenshtein("\xFF\xFE", "\x80\x81"));
    }

    // =========================================================================
    // locale_is_right_to_left Tests
    // =========================================================================

    public function testLocaleIsRightToLeftArabic(): void
    {
        Assert::true(Php85::locale_is_right_to_left('ar'));
    }

    public function testLocaleIsRightToLeftHebrew(): void
    {
        Assert::true(Php85::locale_is_right_to_left('he'));
    }

    public function testLocaleIsRightToLeftPersian(): void
    {
        Assert::true(Php85::locale_is_right_to_left('fa'));
    }

    public function testLocaleIsRightToLeftUrdu(): void
    {
        Assert::true(Php85::locale_is_right_to_left('ur'));
    }

    public function testLocaleIsRightToLeftEnglish(): void
    {
        Assert::false(Php85::locale_is_right_to_left('en'));
    }

    public function testLocaleIsRightToLeftFrench(): void
    {
        Assert::false(Php85::locale_is_right_to_left('fr'));
    }

    public function testLocaleIsRightToLeftWithRegion(): void
    {
        // RTL with region
        Assert::true(Php85::locale_is_right_to_left('ar_EG'));
        Assert::true(Php85::locale_is_right_to_left('he_IL'));
        Assert::true(Php85::locale_is_right_to_left('fa_IR'));

        // LTR with region
        Assert::false(Php85::locale_is_right_to_left('en-US'));
        Assert::false(Php85::locale_is_right_to_left('en_GB'));
        Assert::false(Php85::locale_is_right_to_left('fr_FR'));
    }

    public function testLocaleIsRightToLeftWithExplicitScript(): void
    {
        // RTL scripts explicitly specified
        Assert::true(Php85::locale_is_right_to_left('ar_Arab'));
        Assert::true(Php85::locale_is_right_to_left('en_Hebr'));

        // LTR scripts explicitly specified
        Assert::false(Php85::locale_is_right_to_left('en_Latn'));
        Assert::false(Php85::locale_is_right_to_left('ar_Latn'));
    }

    public function testLocaleIsRightToLeftEmpty(): void
    {
        Assert::false(Php85::locale_is_right_to_left(''));
    }

    public function testLocaleIsRightToLeftAdditionalRtlLanguages(): void
    {
        // Additional RTL languages from the mapping
        Assert::true(Php85::locale_is_right_to_left('yi'));  // Yiddish
        Assert::true(Php85::locale_is_right_to_left('ps'));  // Pashto
        Assert::true(Php85::locale_is_right_to_left('sd'));  // Sindhi
        Assert::true(Php85::locale_is_right_to_left('ug'));  // Uyghur
        Assert::true(Php85::locale_is_right_to_left('dv'));  // Divehi
        Assert::true(Php85::locale_is_right_to_left('ckb')); // Central Kurdish
    }
}

(new Php85Test())->run();
