<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php85;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Php85 polyfill against native PHP 8.5+ functions.
 *
 * These tests verify that the polyfill produces identical results to the
 * native PHP 8.5 functions when running on PHP 8.5+. On earlier PHP versions,
 * these tests serve as documentation of expected behavior.
 *
 * @testCase
 */
class Php85ParityTest extends TestCase
{
    private bool $hasNativeGraphemeLevenshtein;

    private bool $hasNativeLocaleIsRightToLeft;

    protected function setUp(): void
    {
        $this->hasNativeGraphemeLevenshtein = function_exists('grapheme_levenshtein');
        $this->hasNativeLocaleIsRightToLeft = function_exists('locale_is_right_to_left');
    }

    // =========================================================================
    // grapheme_levenshtein Parity Tests
    // =========================================================================

    public function testGraphemeLevenshteinIdenticalStringsParity(): void
    {
        if (!$this->hasNativeGraphemeLevenshtein) {
            Assert::skip('Native grapheme_levenshtein() not available (requires PHP 8.5+)');
        }

        $testCases = [
            ['hello', 'hello'],
            ['', ''],
            ['cafe', 'cafe'],
            ['café', 'café'],
        ];

        foreach ($testCases as [$s1, $s2]) {
            $native = grapheme_levenshtein($s1, $s2);
            $polyfill = Php85::grapheme_levenshtein($s1, $s2);
            Assert::same($native, $polyfill, "Parity failed for '$s1' vs '$s2'");
        }
    }

    public function testGraphemeLevenshteinEmptyStringsParity(): void
    {
        if (!$this->hasNativeGraphemeLevenshtein) {
            Assert::skip('Native grapheme_levenshtein() not available (requires PHP 8.5+)');
        }

        $testCases = [
            ['', 'abc'],
            ['', ''],
            ['abc', ''],
        ];

        foreach ($testCases as [$s1, $s2]) {
            $native = grapheme_levenshtein($s1, $s2);
            $polyfill = Php85::grapheme_levenshtein($s1, $s2);
            Assert::same($native, $polyfill, "Parity failed for '$s1' vs '$s2'");
        }
    }

    public function testGraphemeLevenshteinBasicDistanceParity(): void
    {
        if (!$this->hasNativeGraphemeLevenshtein) {
            Assert::skip('Native grapheme_levenshtein() not available (requires PHP 8.5+)');
        }

        $testCases = [
            ['abc', 'abcd'], // single insertion
            ['abcd', 'abc'], // single deletion
            ['abc', 'axc'], // single replacement
            ['kitten', 'sitting'],
            ['book', 'back'],
        ];

        foreach ($testCases as [$s1, $s2]) {
            $native = grapheme_levenshtein($s1, $s2);
            $polyfill = Php85::grapheme_levenshtein($s1, $s2);
            Assert::same($native, $polyfill, "Parity failed for '$s1' vs '$s2'");
        }
    }

    public function testGraphemeLevenshteinCustomCostsParity(): void
    {
        if (!$this->hasNativeGraphemeLevenshtein) {
            Assert::skip('Native grapheme_levenshtein() not available (requires PHP 8.5+)');
        }

        $testCases = [
            ['abc', 'axc', 2, 3, 2],
            ['abc', 'abcd', 2, 3, 2],
            ['abcd', 'abc', 2, 3, 2],
            ['', 'abc', 5, 1, 1],
            ['abc', '', 1, 1, 5],
        ];

        foreach ($testCases as $case) {
            [$s1, $s2, $insert, $replace, $delete] = $case;
            $native = grapheme_levenshtein($s1, $s2, $insert, $replace, $delete);
            $polyfill = Php85::grapheme_levenshtein($s1, $s2, $insert, $replace, $delete);
            Assert::same($native, $polyfill, "Parity failed for '$s1' vs '$s2' with costs [$insert, $replace, $delete]");
        }
    }

    public function testGraphemeLevenshteinUnicodeStringsParity(): void
    {
        if (!$this->hasNativeGraphemeLevenshtein) {
            Assert::skip('Native grapheme_levenshtein() not available (requires PHP 8.5+)');
        }

        $emoji1 = "👍🏻"; // Thumbs up + light skin tone
        $emoji2 = "👍🏼"; // Thumbs up + medium-light skin tone
        $emoji3 = "👨‍👩‍👧"; // Family emoji
        $emoji4 = "👨‍👩‍👦"; // Family emoji
        $nfc = "é";      // Precomposed é (U+00E9)
        $nfd = "e\u{0301}"; // e + combining acute accent

        $testCases = [
            ['café', 'café'],
            ['café', 'cafe'],
            [$emoji1, $emoji2],
            [$emoji3, $emoji4],
            [$nfc, $nfd],
            ['naïve', 'naive'],
        ];

        foreach ($testCases as [$s1, $s2]) {
            $native = grapheme_levenshtein($s1, $s2);
            $polyfill = Php85::grapheme_levenshtein($s1, $s2);
            Assert::same($native, $polyfill, "Parity failed for unicode strings");
        }
    }

    public function testGraphemeLevenshteinInvalidUtf8Parity(): void
    {
        if (!$this->hasNativeGraphemeLevenshtein) {
            Assert::skip('Native grapheme_levenshtein() not available (requires PHP 8.5+)');
        }

        $testCases = [
            ["\xFF\xFE", 'test'],
            ['test', "\xFF\xFE"],
            ["\xFF\xFE", "\x80\x81"],
        ];

        foreach ($testCases as [$s1, $s2]) {
            $native = grapheme_levenshtein($s1, $s2);
            $polyfill = Php85::grapheme_levenshtein($s1, $s2);
            Assert::same($native, $polyfill, "Parity failed for invalid UTF-8 strings");
        }
    }

    // =========================================================================
    // locale_is_right_to_left Parity Tests
    // =========================================================================

    public function testLocaleIsRightToLeftRtlLanguagesParity(): void
    {
        if (!$this->hasNativeLocaleIsRightToLeft) {
            Assert::skip('Native locale_is_right_to_left() not available (requires PHP 8.5+)');
        }

        $locales = ['ar', 'he', 'fa', 'ur', 'yi', 'ps', 'sd', 'ug', 'dv', 'ku', 'ckb'];

        foreach ($locales as $locale) {
            $native = locale_is_right_to_left($locale);
            $polyfill = Php85::locale_is_right_to_left($locale);
            Assert::same($native, $polyfill, "Parity failed for locale '$locale'");
        }
    }

    public function testLocaleIsRightToLeftLtrLanguagesParity(): void
    {
        if (!$this->hasNativeLocaleIsRightToLeft) {
            Assert::skip('Native locale_is_right_to_left() not available (requires PHP 8.5+)');
        }

        $locales = ['en', 'fr', 'de', 'es', 'zh', 'ja', 'ko', 'ru'];

        foreach ($locales as $locale) {
            $native = locale_is_right_to_left($locale);
            $polyfill = Php85::locale_is_right_to_left($locale);
            Assert::same($native, $polyfill, "Parity failed for locale '$locale'");
        }
    }

    public function testLocaleIsRightToLeftWithRegionParity(): void
    {
        if (!$this->hasNativeLocaleIsRightToLeft) {
            Assert::skip('Native locale_is_right_to_left() not available (requires PHP 8.5+)');
        }

        $testCases = [
            'ar_EG' => true,
            'he_IL' => true,
            'fa_IR' => true,
            'en-US' => false,
            'en_GB' => false,
            'fr_FR' => false,
        ];

        foreach ($testCases as $locale => $expected) {
            $native = locale_is_right_to_left($locale);
            $polyfill = Php85::locale_is_right_to_left($locale);
            Assert::same($native, $polyfill, "Parity failed for locale '$locale'");
        }
    }

    public function testLocaleIsRightToLeftWithExplicitScriptParity(): void
    {
        if (!$this->hasNativeLocaleIsRightToLeft) {
            Assert::skip('Native locale_is_right_to_left() not available (requires PHP 8.5+)');
        }

        $testCases = [
            'ar_Arab' => true,
            'en_Hebr' => true,
            'en_Latn' => false,
            'ar_Latn' => false,
        ];

        foreach ($testCases as $locale => $expected) {
            $native = locale_is_right_to_left($locale);
            $polyfill = Php85::locale_is_right_to_left($locale);
            Assert::same($native, $polyfill, "Parity failed for locale '$locale'");
        }
    }

    public function testLocaleIsRightToLeftEmptyParity(): void
    {
        if (!$this->hasNativeLocaleIsRightToLeft) {
            Assert::skip('Native locale_is_right_to_left() not available (requires PHP 8.5+)');
        }

        $native = locale_is_right_to_left('');
        $polyfill = Php85::locale_is_right_to_left('');
        Assert::same($native, $polyfill, "Parity failed for empty locale");
    }
}

(new Php85ParityTest())->run();
