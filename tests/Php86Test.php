<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php86;
use SortDirection;
use Tester\{Assert, TestCase};
use ValueError;

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for PHP 8.6 polyfill methods.
 *
 * @testCase
 */
class Php86Test extends TestCase
{
    // =========================================================================
    // clamp Tests
    // =========================================================================

    public function testClampReturnsValueWithinRange(): void
    {
        Assert::same(5, Php86::clamp(5, 1, 10));
        Assert::same(1, Php86::clamp(1, 1, 10));
        Assert::same(10, Php86::clamp(10, 1, 10));
    }

    public function testClampReturnsMinWhenValueIsBelowRange(): void
    {
        Assert::same(1, Php86::clamp(-5, 1, 10));
        Assert::same(0, Php86::clamp(PHP_INT_MIN, 0, 10));
    }

    public function testClampReturnsMaxWhenValueIsAboveRange(): void
    {
        Assert::same(10, Php86::clamp(42, 1, 10));
        Assert::same(10, Php86::clamp(PHP_INT_MAX, 0, 10));
    }

    public function testClampWithFloats(): void
    {
        Assert::same(2.5, Php86::clamp(2.5, 1.0, 5.0));
        Assert::same(1.0, Php86::clamp(0.5, 1.0, 5.0));
        Assert::same(5.0, Php86::clamp(9.9, 1.0, 5.0));
    }

    public function testClampWithEqualBounds(): void
    {
        Assert::same(3, Php86::clamp(1, 3, 3));
        Assert::same(3, Php86::clamp(5, 3, 3));
        Assert::same(3, Php86::clamp(3, 3, 3));
    }

    public function testClampThrowsWhenMinIsNan(): void
    {
        Assert::exception(function () {
            Php86::clamp(1.0, NAN, 10.0);
        }, ValueError::class, 'clamp(): Argument #2 ($min) must not be NAN');
    }

    public function testClampThrowsWhenMaxIsNan(): void
    {
        Assert::exception(function () {
            Php86::clamp(1.0, 0.0, NAN);
        }, ValueError::class, 'clamp(): Argument #3 ($max) must not be NAN');
    }

    public function testClampThrowsWhenMinIsGreaterThanMax(): void
    {
        Assert::exception(function () {
            Php86::clamp(5, 10, 1);
        }, ValueError::class, 'clamp(): Argument #2 ($min) must be smaller than or equal to argument #3 ($max)');
    }

    // =========================================================================
    // grapheme_strrev Tests
    // =========================================================================

    public function testGraphemeStrrevReversesAsciiString(): void
    {
        Assert::same('EDCBA', Php86::grapheme_strrev('ABCDE'));
        Assert::same('a', Php86::grapheme_strrev('a'));
    }

    public function testGraphemeStrrevWithEmptyString(): void
    {
        Assert::same('', Php86::grapheme_strrev(''));
    }

    public function testGraphemeStrrevKeepsGraphemeClustersIntact(): void
    {
        // Combining acute accent stays attached to its base character
        Assert::same("e\u{0301}fac", Php86::grapheme_strrev("cafe\u{0301}"));
    }

    public function testGraphemeStrrevWithEmoji(): void
    {
        Assert::same('🍏elppA', Php86::grapheme_strrev('Apple🍏'));

        // Regional indicator pairs (flags) are reversed as single units
        Assert::same('🇳🇨 - 🇨🇳', Php86::grapheme_strrev('🇨🇳 - 🇳🇨'));
    }

    public function testGraphemeStrrevWithEmojiModifier(): void
    {
        if (PHP_VERSION_ID >= 80500) {
            \Tester\Environment::skip(
                'On PHP >= 8.5 the native grapheme_str_split() is used and its handling'
                . ' of emoji modifier sequences depends on the system ICU version.'
            );
        }

        // Emoji with a skin-tone modifier is not broken apart
        Assert::same('C👍🏽A', Php86::grapheme_strrev('A👍🏽C'));
    }

    public function testGraphemeStrrevReturnsFalseOnInvalidString(): void
    {
        if (PHP_VERSION_ID >= 80500) {
            \Tester\Environment::skip(
                'On PHP >= 8.5 the native grapheme_str_split() is used and its handling'
                . ' of invalid UTF-8 differs from the polyfill.'
            );
        }

        Assert::false(@Php86::grapheme_strrev("ab\xff"));
    }

    // =========================================================================
    // SortDirection Tests
    // =========================================================================

    public function testSortDirectionExists(): void
    {
        Assert::true(class_exists(SortDirection::class));
    }

    public function testSortDirectionStubClass(): void
    {
        if (PHP_VERSION_ID >= 80100) {
            \Tester\Environment::skip('The stub class is only used on PHP < 8.1.');
        }

        Assert::false(enum_exists(SortDirection::class));
        Assert::same('ASC', SortDirection::Ascending);
        Assert::same('DESC', SortDirection::Descending);
    }

    public function testSortDirectionEnum(): void
    {
        if (PHP_VERSION_ID < 80100) {
            \Tester\Environment::skip('Enums are only supported on PHP >= 8.1.');
        }

        Assert::true(enum_exists(SortDirection::class));
        Assert::true(SortDirection::Ascending instanceof SortDirection);
        Assert::true(SortDirection::Descending instanceof SortDirection);

        $cases = SortDirection::cases();
        Assert::count(2, $cases);
        Assert::same('Ascending', $cases[0]->name);
        Assert::same('Descending', $cases[1]->name);
    }
}

(new Php86Test())->run();
