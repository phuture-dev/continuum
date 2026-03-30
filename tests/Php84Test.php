<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php84;
use Tester\{Assert, TestCase};
use RoundingMode;

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for Php84 polyfill methods.
 *
 * These tests verify the behavior of the PHP 8.4 polyfill functions
 * for BCMath operations (bcceil, bcfloor, bcround) and the PHP_SBINDIR constant.
 *
 * @testCase
 */
class Php84Test extends TestCase
{
    // =========================================================================
    // Constants Tests
    // =========================================================================

    public function testPhpSbindirConstant(): void
    {
        Assert::same('/usr/local/sbin', Php84::PHP_SBINDIR);
    }

    // =========================================================================
    // bcceil Tests
    // =========================================================================

    public function testBcceilPositiveNumbers(): void
    {
        // Positive integers (no change)
        Assert::same('5', Php84::bcceil('5'));
        Assert::same('0', Php84::bcceil('0'));
        Assert::same('100', Php84::bcceil('100'));

        // Positive decimals (round up)
        Assert::same('2', Php84::bcceil('1.1'));
        Assert::same('2', Php84::bcceil('1.5'));
        Assert::same('2', Php84::bcceil('1.9'));
        Assert::same('1', Php84::bcceil('0.1'));
        Assert::same('1', Php84::bcceil('0.001'));
    }

    public function testBcceilNegativeNumbers(): void
    {
        // Negative integers (no change)
        Assert::same('-5', Php84::bcceil('-5'));
        Assert::same('-100', Php84::bcceil('-100'));

        // Negative decimals (round toward zero)
        Assert::same('-1', Php84::bcceil('-1.1'));
        Assert::same('-1', Php84::bcceil('-1.5'));
        Assert::same('-1', Php84::bcceil('-1.9'));
        Assert::same('0', Php84::bcceil('-0.1'));
        Assert::same('0', Php84::bcceil('-0.9'));
    }

    public function testBcceilZeroAndEdgeCases(): void
    {
        Assert::same('0', Php84::bcceil('0'));
        Assert::same('0', Php84::bcceil('0.0'));
        Assert::same('1', Php84::bcceil('0.0000001'));
    }

    // =========================================================================
    // bcfloor Tests
    // =========================================================================

    public function testBcfloorPositiveNumbers(): void
    {
        // Positive integers (no change)
        Assert::same('5', Php84::bcfloor('5'));
        Assert::same('0', Php84::bcfloor('0'));
        Assert::same('100', Php84::bcfloor('100'));

        // Positive decimals (round down)
        Assert::same('1', Php84::bcfloor('1.1'));
        Assert::same('1', Php84::bcfloor('1.5'));
        Assert::same('1', Php84::bcfloor('1.9'));
        Assert::same('0', Php84::bcfloor('0.1'));
        Assert::same('0', Php84::bcfloor('0.999'));
    }

    public function testBcfloorNegativeNumbers(): void
    {
        // Negative integers (no change)
        Assert::same('-5', Php84::bcfloor('-5'));
        Assert::same('-100', Php84::bcfloor('-100'));

        // Negative decimals (round away from zero)
        Assert::same('-2', Php84::bcfloor('-1.1'));
        Assert::same('-2', Php84::bcfloor('-1.5'));
        Assert::same('-2', Php84::bcfloor('-1.9'));
        Assert::same('-1', Php84::bcfloor('-0.1'));
        Assert::same('-1', Php84::bcfloor('-0.001'));
    }

    public function testBcfloorZeroAndEdgeCases(): void
    {
        Assert::same('0', Php84::bcfloor('0'));
        Assert::same('0', Php84::bcfloor('0.0'));
        Assert::same('0', Php84::bcfloor('0.999999'));
    }

    // =========================================================================
    // bcround Tests - Default Mode (HalfAwayFromZero)
    // =========================================================================

    public function testBcroundDefaultModePositive(): void
    {
        // Round down (< 0.5)
        Assert::same('1', Php84::bcround('1.4'));
        Assert::same('1', Php84::bcround('1.49'));

        // Round up (>= 0.5)
        Assert::same('2', Php84::bcround('1.5'));
        Assert::same('2', Php84::bcround('1.51'));
        Assert::same('2', Php84::bcround('1.6'));
        Assert::same('2', Php84::bcround('1.9'));
    }

    public function testBcroundDefaultModeNegative(): void
    {
        // Round toward zero (< 0.5)
        Assert::same('-1', Php84::bcround('-1.4'));
        Assert::same('-1', Php84::bcround('-1.49'));

        // Round away from zero (>= 0.5)
        Assert::same('-2', Php84::bcround('-1.5'));
        Assert::same('-2', Php84::bcround('-1.51'));
        Assert::same('-2', Php84::bcround('-1.6'));
    }

    public function testBcroundWithPrecision(): void
    {
        // Precision 1
        Assert::same('1.2', Php84::bcround('1.15', 1));
        Assert::same('1.2', Php84::bcround('1.24', 1));
        Assert::same('1.3', Php84::bcround('1.25', 1));

        // Precision 2
        Assert::same('1.23', Php84::bcround('1.234', 2));
        Assert::same('1.24', Php84::bcround('1.235', 2));
    }

    // =========================================================================
    // bcround Tests - HalfTowardsZero Mode
    // =========================================================================

    public function testBcroundHalfTowardsZeroPositive(): void
    {
        // < 0.5 rounds down
        Assert::same('1', Php84::bcround('1.4', 0, RoundingMode::HalfTowardsZero));
        Assert::same('1', Php84::bcround('1.49', 0, RoundingMode::HalfTowardsZero));

        // = 0.5 rounds toward zero (down for positive)
        Assert::same('1', Php84::bcround('1.5', 0, RoundingMode::HalfTowardsZero));

        // > 0.5 rounds up
        Assert::same('2', Php84::bcround('1.51', 0, RoundingMode::HalfTowardsZero));
        Assert::same('2', Php84::bcround('1.6', 0, RoundingMode::HalfTowardsZero));
    }

    public function testBcroundHalfTowardsZeroNegative(): void
    {
        // < 0.5 rounds up (toward zero)
        Assert::same('-1', Php84::bcround('-1.4', 0, RoundingMode::HalfTowardsZero));
        Assert::same('-1', Php84::bcround('-1.49', 0, RoundingMode::HalfTowardsZero));

        // = 0.5 rounds toward zero (up for negative)
        Assert::same('-1', Php84::bcround('-1.5', 0, RoundingMode::HalfTowardsZero));

        // > 0.5 rounds down (away from zero)
        Assert::same('-2', Php84::bcround('-1.51', 0, RoundingMode::HalfTowardsZero));
        Assert::same('-2', Php84::bcround('-1.6', 0, RoundingMode::HalfTowardsZero));
    }

    // =========================================================================
    // bcround Tests - HalfEven Mode (Banker's Rounding)
    // =========================================================================

    public function testBcroundHalfEvenPositive(): void
    {
        // < 0.5 rounds down
        Assert::same('1', Php84::bcround('1.4', 0, RoundingMode::HalfEven));
        Assert::same('1', Php84::bcround('1.49', 0, RoundingMode::HalfEven));

        // = 0.5 rounds to nearest even
        Assert::same('2', Php84::bcround('1.5', 0, RoundingMode::HalfEven));  // 1 -> 2 (2 is even)
        Assert::same('2', Php84::bcround('2.5', 0, RoundingMode::HalfEven));  // 2 -> 2 (2 is even, stays)
        Assert::same('4', Php84::bcround('3.5', 0, RoundingMode::HalfEven));  // 3 -> 4 (4 is even)
        Assert::same('4', Php84::bcround('4.5', 0, RoundingMode::HalfEven));  // 4 -> 4 (4 is even, stays)

        // > 0.5 rounds up
        Assert::same('2', Php84::bcround('1.51', 0, RoundingMode::HalfEven));
        Assert::same('2', Php84::bcround('1.6', 0, RoundingMode::HalfEven));
    }

    public function testBcroundHalfEvenNegative(): void
    {
        // < 0.5 rounds toward zero
        Assert::same('-1', Php84::bcround('-1.4', 0, RoundingMode::HalfEven));

        // = 0.5 rounds to nearest even
        Assert::same('-2', Php84::bcround('-1.5', 0, RoundingMode::HalfEven));  // -1 -> -2 (2 is even)
        Assert::same('-2', Php84::bcround('-2.5', 0, RoundingMode::HalfEven));  // -2 -> -2 (2 is even, stays)

        // > 0.5 rounds away from zero
        Assert::same('-2', Php84::bcround('-1.51', 0, RoundingMode::HalfEven));
    }

    // =========================================================================
    // bcround Tests - HalfOdd Mode
    // =========================================================================

    public function testBcroundHalfOddPositive(): void
    {
        // < 0.5 rounds down
        Assert::same('1', Php84::bcround('1.4', 0, RoundingMode::HalfOdd));

        // = 0.5 rounds to nearest odd
        Assert::same('1', Php84::bcround('1.5', 0, RoundingMode::HalfOdd));  // 1 -> 1 (1 is odd, stays)
        Assert::same('3', Php84::bcround('2.5', 0, RoundingMode::HalfOdd));  // 2 -> 3 (3 is odd)
        Assert::same('3', Php84::bcround('3.5', 0, RoundingMode::HalfOdd));  // 3 -> 3 (3 is odd, stays)
        Assert::same('5', Php84::bcround('4.5', 0, RoundingMode::HalfOdd));  // 4 -> 5 (5 is odd)

        // > 0.5 rounds up
        Assert::same('2', Php84::bcround('1.51', 0, RoundingMode::HalfOdd));
    }

    public function testBcroundHalfOddNegative(): void
    {
        // < 0.5 rounds toward zero
        Assert::same('-1', Php84::bcround('-1.4', 0, RoundingMode::HalfOdd));

        // = 0.5 rounds to nearest odd
        Assert::same('-1', Php84::bcround('-1.5', 0, RoundingMode::HalfOdd));  // -1 -> -1 (1 is odd, stays)
        Assert::same('-3', Php84::bcround('-2.5', 0, RoundingMode::HalfOdd));  // -2 -> -3 (3 is odd)

        // > 0.5 rounds away from zero
        Assert::same('-2', Php84::bcround('-1.51', 0, RoundingMode::HalfOdd));
    }

    // =========================================================================
    // bcround Tests - PositiveInfinity Mode (Ceiling)
    // =========================================================================

    public function testBcroundPositiveInfinity(): void
    {
        // Always rounds toward positive infinity
        Assert::same('2', Php84::bcround('1.1', 0, RoundingMode::PositiveInfinity));
        Assert::same('2', Php84::bcround('1.5', 0, RoundingMode::PositiveInfinity));
        Assert::same('2', Php84::bcround('1.9', 0, RoundingMode::PositiveInfinity));
        Assert::same('1', Php84::bcround('1.0', 0, RoundingMode::PositiveInfinity));

        // Negative numbers also round toward positive infinity
        Assert::same('0', Php84::bcround('-0.1', 0, RoundingMode::PositiveInfinity));
        Assert::same('-1', Php84::bcround('-1.0', 0, RoundingMode::PositiveInfinity));
        Assert::same('-1', Php84::bcround('-1.5', 0, RoundingMode::PositiveInfinity));
        Assert::same('-1', Php84::bcround('-1.9', 0, RoundingMode::PositiveInfinity));
    }

    // =========================================================================
    // bcround Tests - NegativeInfinity Mode (Floor)
    // =========================================================================

    public function testBcroundNegativeInfinity(): void
    {
        // Always rounds toward negative infinity
        Assert::same('1', Php84::bcround('1.0', 0, RoundingMode::NegativeInfinity));
        Assert::same('1', Php84::bcround('1.1', 0, RoundingMode::NegativeInfinity));
        Assert::same('1', Php84::bcround('1.5', 0, RoundingMode::NegativeInfinity));
        Assert::same('1', Php84::bcround('1.9', 0, RoundingMode::NegativeInfinity));

        // Negative numbers also round toward negative infinity
        Assert::same('-1', Php84::bcround('-0.1', 0, RoundingMode::NegativeInfinity));
        Assert::same('-2', Php84::bcround('-1.1', 0, RoundingMode::NegativeInfinity));
        Assert::same('-2', Php84::bcround('-1.5', 0, RoundingMode::NegativeInfinity));
        Assert::same('-2', Php84::bcround('-1.9', 0, RoundingMode::NegativeInfinity));
    }

    // =========================================================================
    // bcround Tests - TowardsZero Mode (Truncate)
    // =========================================================================

    public function testBcroundTowardsZero(): void
    {
        // Always rounds toward zero (truncates)
        Assert::same('1', Php84::bcround('1.0', 0, RoundingMode::TowardsZero));
        Assert::same('1', Php84::bcround('1.1', 0, RoundingMode::TowardsZero));
        Assert::same('1', Php84::bcround('1.5', 0, RoundingMode::TowardsZero));
        Assert::same('1', Php84::bcround('1.9', 0, RoundingMode::TowardsZero));

        Assert::same('-1', Php84::bcround('-1.0', 0, RoundingMode::TowardsZero));
        Assert::same('-1', Php84::bcround('-1.1', 0, RoundingMode::TowardsZero));
        Assert::same('-1', Php84::bcround('-1.5', 0, RoundingMode::TowardsZero));
        Assert::same('-1', Php84::bcround('-1.9', 0, RoundingMode::TowardsZero));
    }

    // =========================================================================
    // bcround Tests - AwayFromZero Mode
    // =========================================================================

    public function testBcroundAwayFromZero(): void
    {
        // Always rounds away from zero if there's any fractional part
        Assert::same('1', Php84::bcround('1.0', 0, RoundingMode::AwayFromZero));
        Assert::same('2', Php84::bcround('1.1', 0, RoundingMode::AwayFromZero));
        Assert::same('2', Php84::bcround('1.5', 0, RoundingMode::AwayFromZero));
        Assert::same('2', Php84::bcround('1.9', 0, RoundingMode::AwayFromZero));

        Assert::same('-1', Php84::bcround('-1.0', 0, RoundingMode::AwayFromZero));
        Assert::same('-2', Php84::bcround('-1.1', 0, RoundingMode::AwayFromZero));
        Assert::same('-2', Php84::bcround('-1.5', 0, RoundingMode::AwayFromZero));
        Assert::same('-2', Php84::bcround('-1.9', 0, RoundingMode::AwayFromZero));
    }
}

(new Php84Test())->run();
