<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use ValueError;
use Time\TimeException;
use Tester\{Assert, TestCase};
use Phuture\Continuum\Time\DurationMath;

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for the DurationMath pure-math engine.
 *
 * These tests exercise the validation, arithmetic and parsing logic without
 * going through the Time\Duration class shell, so they run on every PHP
 * version from 8.0 upward.
 *
 * @testCase
 */
class DurationMathTest extends TestCase
{
    // =========================================================================
    // maximumSeconds / word size
    // =========================================================================

    public function testMaximumSecondsOn64Bit(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::same(9_223_372_035, DurationMath::maximumSeconds());
    }

    public function testMaximumSecondsOn32Bit(): void
    {
        if (PHP_INT_SIZE >= 8) {
            \Tester\Environment::skip('32-bit only test.');
        }

        Assert::same(PHP_INT_MAX, DurationMath::maximumSeconds());
    }

    // =========================================================================
    // create / normalise
    // =========================================================================

    public function testCreateNormalisesZeroToNonNegative(): void
    {
        $result = DurationMath::create(0, 0, true);
        Assert::false($result['negative']);
    }

    public function testCreateAcceptsNanosecondBoundaries(): void
    {
        $zero = DurationMath::create(5, 0, false);
        Assert::same(0, $zero['nanoseconds']);

        $max = DurationMath::create(5, 999_999_999, false);
        Assert::same(999_999_999, $max['nanoseconds']);
    }

    public function testCreateAcceptsMaximumSeconds(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        $result = DurationMath::create(DurationMath::maximumSeconds(), 0, false);
        Assert::same(DurationMath::maximumSeconds(), $result['seconds']);
    }

    public function testCreateThrowsOnSecondsAboveMaximum(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::create(DurationMath::maximumSeconds() + 1, 0, false);
        }, TimeException::class);
    }

    public function testCreateThrowsOnNegativeSeconds(): void
    {
        Assert::exception(function () {
            DurationMath::create(-1, 0, false);
        }, ValueError::class);
    }

    public function testCreateThrowsOnNegativeNanoseconds(): void
    {
        Assert::exception(function () {
            DurationMath::create(0, -1, false);
        }, ValueError::class);
    }

    public function testCreateThrowsOnNanosecondsAboveBoundary(): void
    {
        Assert::exception(function () {
            DurationMath::create(0, 1_000_000_000, false);
        }, ValueError::class);
    }

    // =========================================================================
    // fromSeconds
    // =========================================================================

    public function testFromSecondsBasicValues(): void
    {
        Assert::same(['seconds' => 0, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromSeconds(0, 0));
        Assert::same(['seconds' => 1, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromSeconds(1));
        Assert::same(['seconds' => 1, 'nanoseconds' => 999_999_999, 'negative' => false], DurationMath::fromSeconds(1, 999_999_999));
    }

    public function testFromSecondsThrowsOnNegative(): void
    {
        Assert::exception(function () {
            DurationMath::fromSeconds(-1);
        }, ValueError::class);

        Assert::exception(function () {
            DurationMath::fromSeconds(0, -1);
        }, ValueError::class);
    }

    public function testFromSecondsThrowsOnNanosecondsAboveBoundary(): void
    {
        Assert::exception(function () {
            DurationMath::fromSeconds(0, 1_000_000_000);
        }, ValueError::class);
    }

    // =========================================================================
    // fromNanoseconds
    // =========================================================================

    public function testFromNanosecondsBasicValues(): void
    {
        Assert::same(['seconds' => 0, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromNanoseconds(0));
        Assert::same(['seconds' => 0, 'nanoseconds' => 999_999_999, 'negative' => false], DurationMath::fromNanoseconds(999_999_999));
        Assert::same(['seconds' => 1, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromNanoseconds(1_000_000_000));
        Assert::same(['seconds' => 1, 'nanoseconds' => 500_000_000, 'negative' => false], DurationMath::fromNanoseconds(1_500_000_000));
    }

    public function testFromNanosecondsAcceptsMaximum(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        $result = DurationMath::fromNanoseconds(9_223_372_035_999_999_999);
        Assert::same(DurationMath::maximumSeconds(), $result['seconds']);
        Assert::same(999_999_999, $result['nanoseconds']);
    }

    public function testFromNanosecondsThrowsOnOverflow(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::fromNanoseconds(PHP_INT_MAX);
        }, TimeException::class);
    }

    public function testFromNanosecondsThrowsOnNegative(): void
    {
        Assert::exception(function () {
            DurationMath::fromNanoseconds(-1);
        }, ValueError::class);
    }

    // =========================================================================
    // fromMicroseconds
    // =========================================================================

    public function testFromMicrosecondsBasicValues(): void
    {
        Assert::same(['seconds' => 0, 'nanoseconds' => 1_000, 'negative' => false], DurationMath::fromMicroseconds(1));
        Assert::same(['seconds' => 1, 'nanoseconds' => 500_000_000, 'negative' => false], DurationMath::fromMicroseconds(1_500_000));
    }

    public function testFromMicrosecondsAcceptsNearMaximum(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        $result = DurationMath::fromMicroseconds(9_223_372_035_999_999);
        Assert::same(DurationMath::maximumSeconds(), $result['seconds']);
        Assert::same(999_999_000, $result['nanoseconds']);
    }

    public function testFromMicrosecondsThrowsOnOverflow(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::fromMicroseconds(9_223_372_036_000_000);
        }, TimeException::class);
    }

    // =========================================================================
    // fromMilliseconds
    // =========================================================================

    public function testFromMillisecondsBasicValues(): void
    {
        Assert::same(['seconds' => 0, 'nanoseconds' => 500_000_000, 'negative' => false], DurationMath::fromMilliseconds(500));
        Assert::same(['seconds' => 2, 'nanoseconds' => 500_000_000, 'negative' => false], DurationMath::fromMilliseconds(2_500));
    }

    public function testFromMillisecondsAcceptsNearMaximum(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        $result = DurationMath::fromMilliseconds(9_223_372_035_999);
        Assert::same(DurationMath::maximumSeconds(), $result['seconds']);
        Assert::same(999_000_000, $result['nanoseconds']);
    }

    public function testFromMillisecondsThrowsOnOverflow(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::fromMilliseconds(9_223_372_036_000);
        }, TimeException::class);
    }

    // =========================================================================
    // fromMinutes / fromHours
    // =========================================================================

    public function testFromMinutesAcceptsBoundary(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        $result = DurationMath::fromMinutes(153_722_867);
        Assert::same(153_722_867 * 60, $result['seconds']);
    }

    public function testFromMinutesThrowsOverflow(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::fromMinutes(153_722_868);
        }, TimeException::class);
    }

    public function testFromMinutesThrowsOnNegative(): void
    {
        Assert::exception(function () {
            DurationMath::fromMinutes(-1);
        }, ValueError::class);
    }

    public function testFromHoursAcceptsBoundary(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        $result = DurationMath::fromHours(2_562_047);
        Assert::same(2_562_047 * 3600, $result['seconds']);
    }

    public function testFromHoursThrowsOverflow(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::fromHours(2_562_048);
        }, TimeException::class);
    }

    public function testFromHoursThrowsOnNegative(): void
    {
        Assert::exception(function () {
            DurationMath::fromHours(-1);
        }, ValueError::class);
    }

    // =========================================================================
    // add
    // =========================================================================

    public function testAddCarryNanoseconds(): void
    {
        $result = DurationMath::add(
            DurationMath::fromSeconds(0, 999_999_999),
            DurationMath::fromSeconds(0, 1)
        );
        Assert::same(['seconds' => 1, 'nanoseconds' => 0, 'negative' => false], $result);
    }

    public function testAddBorrowNanoseconds(): void
    {
        $result = DurationMath::add(
            DurationMath::fromSeconds(1),
            DurationMath::negate(DurationMath::fromSeconds(0, 1))
        );
        Assert::same(['seconds' => 0, 'nanoseconds' => 999_999_999, 'negative' => false], $result);
    }

    public function testAddEqualMagnitudesOppositeSignsYieldsZero(): void
    {
        $result = DurationMath::add(
            DurationMath::fromSeconds(5),
            DurationMath::negate(DurationMath::fromSeconds(5))
        );
        Assert::same(['seconds' => 0, 'nanoseconds' => 0, 'negative' => false], $result);
    }

    public function testAddSignFollowsLargerMagnitude(): void
    {
        $result = DurationMath::add(
            DurationMath::negate(DurationMath::fromSeconds(10)),
            DurationMath::fromSeconds(3)
        );
        Assert::true($result['negative']);
        Assert::same(7, $result['seconds']);

        $result = DurationMath::add(
            DurationMath::fromSeconds(10),
            DurationMath::negate(DurationMath::fromSeconds(3))
        );
        Assert::false($result['negative']);
        Assert::same(7, $result['seconds']);
    }

    public function testAddOverflow(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::add(
                DurationMath::fromSeconds(DurationMath::maximumSeconds(), 999_999_999),
                DurationMath::fromSeconds(0, 1)
            );
        }, TimeException::class);

        Assert::exception(function () {
            DurationMath::add(
                DurationMath::fromSeconds(DurationMath::maximumSeconds()),
                DurationMath::fromSeconds(1)
            );
        }, TimeException::class);
    }

    public function testAddZeroIsIdentity(): void
    {
        $value = DurationMath::fromSeconds(5, 100);
        $result = DurationMath::add($value, DurationMath::fromSeconds(0));
        Assert::same($value, $result);
    }

    public function testAddCommutativity(): void
    {
        $a = DurationMath::fromSeconds(3, 200_000_000);
        $b = DurationMath::fromSeconds(1, 800_000_000);

        Assert::same(DurationMath::add($a, $b), DurationMath::add($b, $a));
    }

    // =========================================================================
    // sub
    // =========================================================================

    public function testSubBasic(): void
    {
        $result = DurationMath::subtract(
            DurationMath::fromSeconds(1),
            DurationMath::fromSeconds(2)
        );
        Assert::true($result['negative']);
        Assert::same(1, $result['seconds']);
    }

    public function testSubSelfYieldsNonNegativeZero(): void
    {
        $value = DurationMath::fromSeconds(5);
        $result = DurationMath::subtract($value, $value);
        Assert::same(['seconds' => 0, 'nanoseconds' => 0, 'negative' => false], $result);
    }

    public function testSubZeroFromValue(): void
    {
        $value = DurationMath::fromSeconds(5);
        $result = DurationMath::subtract($value, DurationMath::fromSeconds(0));
        Assert::same($value, $result);
    }

    public function testSubMatchesAddNegate(): void
    {
        $a = DurationMath::fromSeconds(7, 300_000_000);
        $b = DurationMath::fromSeconds(2, 500_000_000);

        Assert::same(
            DurationMath::subtract($a, $b),
            DurationMath::add($a, DurationMath::negate($b))
        );
    }

    // =========================================================================
    // negate / absolute
    // =========================================================================

    public function testNegateZeroStaysNonNegative(): void
    {
        $result = DurationMath::negate(DurationMath::fromSeconds(0));
        Assert::false($result['negative']);
    }

    public function testNegateFlipsSign(): void
    {
        $positive = DurationMath::fromSeconds(5);
        $negated = DurationMath::negate($positive);
        Assert::true($negated['negative']);

        $double = DurationMath::negate($negated);
        Assert::false($double['negative']);
        Assert::same($positive, $double);
    }

    public function testAbsoluteClearsNegative(): void
    {
        $negative = DurationMath::negate(DurationMath::fromSeconds(5));
        $absolute = DurationMath::absolute($negative);
        Assert::false($absolute['negative']);
        Assert::same(5, $absolute['seconds']);
    }

    // =========================================================================
    // multiplyBy
    // =========================================================================

    public function testMultiplyByZeroOnNegativeYieldsNonNegativeZero(): void
    {
        $negative = DurationMath::negate(DurationMath::fromSeconds(5));
        $result = DurationMath::multiplyBy($negative, 0);
        Assert::same(['seconds' => 0, 'nanoseconds' => 0, 'negative' => false], $result);
    }

    public function testMultiplyByOneIsIdentity(): void
    {
        $value = DurationMath::fromSeconds(5, 100);
        Assert::same($value, DurationMath::multiplyBy($value, 1));
    }

    public function testMultiplyByCarry(): void
    {
        $result = DurationMath::multiplyBy(DurationMath::fromSeconds(0, 600_000_000), 2);
        Assert::same(['seconds' => 1, 'nanoseconds' => 200_000_000, 'negative' => false], $result);
    }

    public function testMultiplyByPreservesSign(): void
    {
        $negative = DurationMath::negate(DurationMath::fromSeconds(3));
        $result = DurationMath::multiplyBy($negative, 2);
        Assert::true($result['negative']);
        Assert::same(6, $result['seconds']);
    }

    public function testMultiplyByThrowsOnNegative(): void
    {
        Assert::exception(function () {
            DurationMath::multiplyBy(DurationMath::fromSeconds(1), -1);
        }, ValueError::class);
    }

    public function testMultiplyByOverflow(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::multiplyBy(DurationMath::fromSeconds(DurationMath::maximumSeconds()), 2);
        }, TimeException::class);

        Assert::exception(function () {
            DurationMath::multiplyBy(DurationMath::fromSeconds(0, 999_999_999), PHP_INT_MAX);
        }, TimeException::class);
    }

    public function testMultiplyByRfcExample(): void
    {
        $result = DurationMath::multiplyBy(DurationMath::fromMilliseconds(100), 2 ** 5);
        Assert::same(3, $result['seconds']);
        Assert::same(200_000_000, $result['nanoseconds']);
    }

    // =========================================================================
    // divideBy
    // =========================================================================

    public function testDivideByRfcExample(): void
    {
        $result = DurationMath::divideBy(DurationMath::fromSeconds(1), 2);
        Assert::same(['seconds' => 0, 'nanoseconds' => 500_000_000, 'negative' => false], $result);
    }

    public function testDivideByTruncatesTowardZero(): void
    {
        $result = DurationMath::divideBy(DurationMath::fromSeconds(1), 3);
        Assert::same(['seconds' => 0, 'nanoseconds' => 333_333_333, 'negative' => false], $result);
    }

    public function testDivideByNanosecondFractionYieldsNonNegativeZero(): void
    {
        $result = DurationMath::divideBy(DurationMath::fromNanoseconds(1), 2);
        Assert::same(['seconds' => 0, 'nanoseconds' => 0, 'negative' => false], $result);
    }

    public function testDivideByNegativeKeepsSign(): void
    {
        $negative = DurationMath::negate(DurationMath::fromSeconds(1));
        $result = DurationMath::divideBy($negative, 2);
        Assert::true($result['negative']);
        Assert::same(0, $result['seconds']);
        Assert::same(500_000_000, $result['nanoseconds']);
    }

    public function testDivideByOneIsIdentity(): void
    {
        $value = DurationMath::fromSeconds(5, 100);
        Assert::same($value, DurationMath::divideBy($value, 1));
    }

    public function testDivideByMaximum(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        $result = DurationMath::divideBy(
            DurationMath::fromSeconds(DurationMath::maximumSeconds(), 999_999_999),
            2
        );
        Assert::same(4_611_686_017, $result['seconds']);
        Assert::same(999_999_999, $result['nanoseconds']);
    }

    public function testDivideByThrowsOnZero(): void
    {
        Assert::exception(function () {
            DurationMath::divideBy(DurationMath::fromSeconds(1), 0);
        }, ValueError::class);
    }

    public function testDivideByThrowsOnNegative(): void
    {
        Assert::exception(function () {
            DurationMath::divideBy(DurationMath::fromSeconds(1), -1);
        }, ValueError::class);
    }

    // =========================================================================
    // compare
    // =========================================================================

    public function testCompareEqual(): void
    {
        Assert::same(0, DurationMath::compare(DurationMath::fromSeconds(5), DurationMath::fromSeconds(5)));
    }

    public function testCompareBySeconds(): void
    {
        Assert::same(-1, DurationMath::compare(DurationMath::fromSeconds(1), DurationMath::fromSeconds(2)));
        Assert::same(1, DurationMath::compare(DurationMath::fromSeconds(2), DurationMath::fromSeconds(1)));
    }

    public function testCompareByNanoseconds(): void
    {
        Assert::same(-1, DurationMath::compare(DurationMath::fromSeconds(0, 1), DurationMath::fromSeconds(0, 2)));
        Assert::same(1, DurationMath::compare(DurationMath::fromSeconds(0, 2), DurationMath::fromSeconds(0, 1)));
    }

    public function testCompareNegativeVsNegativeReversed(): void
    {
        $a = DurationMath::negate(DurationMath::fromSeconds(5));
        $b = DurationMath::negate(DurationMath::fromSeconds(1));
        Assert::same(-1, DurationMath::compare($a, $b));
        Assert::same(1, DurationMath::compare($b, $a));
    }

    public function testCompareNegativeVsPositive(): void
    {
        Assert::same(-1, DurationMath::compare(
            DurationMath::negate(DurationMath::fromSeconds(1)),
            DurationMath::fromSeconds(1)
        ));
        Assert::same(1, DurationMath::compare(
            DurationMath::fromSeconds(1),
            DurationMath::negate(DurationMath::fromSeconds(1))
        ));
    }

    public function testCompareZeroVsZero(): void
    {
        Assert::same(0, DurationMath::compare(DurationMath::fromSeconds(0), DurationMath::fromSeconds(0)));
    }

    public function testCompareReturnsStrictlyBoundedValues(): void
    {
        $a = DurationMath::fromSeconds(1);
        $b = DurationMath::fromSeconds(100);
        $result = DurationMath::compare($a, $b);
        Assert::true($result === -1 || $result === 0 || $result === 1);
    }

    // =========================================================================
    // comparableSeconds / comparableNanoseconds
    // =========================================================================

    public function testComparableValuesPositive(): void
    {
        $duration = DurationMath::fromSeconds(5, 200_000_000);
        Assert::same(5, DurationMath::comparableSeconds($duration));
        Assert::same(200_000_000, DurationMath::comparableNanoseconds($duration));
    }

    public function testComparableValuesNegative(): void
    {
        $duration = DurationMath::negate(DurationMath::fromSeconds(5, 200_000_000));
        Assert::same(-5, DurationMath::comparableSeconds($duration));
        Assert::same(-200_000_000, DurationMath::comparableNanoseconds($duration));
    }

    // =========================================================================
    // ISO-8601 parsing
    // =========================================================================

    public function testIso8601Accepted(): void
    {
        Assert::same(['seconds' => 0, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromIso8601DurationString('PT0S'));
        Assert::same(['seconds' => 3600, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromIso8601DurationString('PT1H'));
        Assert::same(['seconds' => 1800, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromIso8601DurationString('PT30M'));
        Assert::same(['seconds' => 15, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromIso8601DurationString('PT15S'));
        Assert::same(['seconds' => 5400, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromIso8601DurationString('PT1H30M'));
        Assert::same(['seconds' => 5415, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromIso8601DurationString('PT1H30M15S'));
        Assert::same(['seconds' => 3600, 'nanoseconds' => 0, 'negative' => false], DurationMath::fromIso8601DurationString('PT01H'));
        Assert::same(['seconds' => 1, 'nanoseconds' => 500_000_000, 'negative' => false], DurationMath::fromIso8601DurationString('PT1.5S'));
        Assert::same(['seconds' => 0, 'nanoseconds' => 500_000_000, 'negative' => false], DurationMath::fromIso8601DurationString('PT0,5S'));
        Assert::same(['seconds' => 0, 'nanoseconds' => 1, 'negative' => false], DurationMath::fromIso8601DurationString('PT0.000000001S'));
    }

    public function testIso8601RejectsInvalid(): void
    {
        $invalid = [
            'PT0.1234567891S',
            'PT1.5H',
            'PT1.5M',
            'P1D',
            'P1W',
            'P1M',
            'P1Y',
            'P1DT1H',
            '',
            'P',
            'PT',
            'T1H',
            '-PT1S',
            '+PT1S',
            'pt1h',
            'PT1h',
            'PT30S1H',
            'PT1H1H',
            ' PT5S',
            "PT5S\n",
        ];

        foreach ($invalid as $input) {
            Assert::exception(function () use ($input) {
                DurationMath::fromIso8601DurationString($input);
            }, TimeException::class);
        }
    }

    public function testIso8601Overflow(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            DurationMath::fromIso8601DurationString('PT9999999999H');
        }, TimeException::class);

        Assert::exception(function () {
            DurationMath::fromIso8601DurationString('PT99999999999999999999S');
        }, TimeException::class);
    }
}

(new DurationMathTest())->run();
