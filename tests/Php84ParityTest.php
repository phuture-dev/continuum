<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php84;
use Tester\{Assert, TestCase};
use RoundingMode;

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Php84 polyfill against native PHP 8.4+ functions.
 *
 * These tests verify that the polyfill produces identical results to the
 * native PHP 8.4 functions when running on PHP 8.4+. On earlier PHP versions,
 * these tests serve as documentation of expected behavior.
 *
 * @testCase
 */
class Php84ParityTest extends TestCase
{
    private bool $hasNativeBcmath;

    protected function setUp(): void
    {
        $this->hasNativeBcmath = function_exists('bcceil') && function_exists('bcfloor') && function_exists('bcround');
    }

    // =========================================================================
    // bcceil Parity Tests
    // =========================================================================

    public function testBcceilPositiveNumbersParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcceil() not available');
            return;
        }

        $testCases = [
            '5',
            '0',
            '100',
            '1.1',
            '1.5',
            '1.9',
            '0.1',
            '0.001',
        ];

        foreach ($testCases as $input) {
            $native = bcceil($input);
            $polyfill = Php84::bcceil($input);
            Assert::same($native, $polyfill, "Parity failed for bcceil('$input')");
        }
    }

    public function testBcceilNegativeNumbersParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcceil() not available');
            return;
        }

        $testCases = [
            '-5',
            '-100',
            '-1.1',
            '-1.5',
            '-1.9',
            '-0.1',
            '-0.9',
        ];

        foreach ($testCases as $input) {
            $native = bcceil($input);
            $polyfill = Php84::bcceil($input);
            Assert::same($native, $polyfill, "Parity failed for bcceil('$input')");
        }
    }

    public function testBcceilZeroAndEdgeCasesParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcceil() not available');
            return;
        }

        $testCases = [
            '0',
            '0.0',
            '0.0000001',
        ];

        foreach ($testCases as $input) {
            $native = bcceil($input);
            $polyfill = Php84::bcceil($input);
            Assert::same($native, $polyfill, "Parity failed for bcceil('$input')");
        }
    }

    // =========================================================================
    // bcfloor Parity Tests
    // =========================================================================

    public function testBcfloorPositiveNumbersParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcfloor() not available');
            return;
        }

        $testCases = [
            '5',
            '0',
            '100',
            '1.1',
            '1.5',
            '1.9',
            '0.1',
            '0.999',
        ];

        foreach ($testCases as $input) {
            $native = bcfloor($input);
            $polyfill = Php84::bcfloor($input);
            Assert::same($native, $polyfill, "Parity failed for bcfloor('$input')");
        }
    }

    public function testBcfloorNegativeNumbersParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcfloor() not available');
            return;
        }

        $testCases = [
            '-5',
            '-100',
            '-1.1',
            '-1.5',
            '-1.9',
            '-0.1',
            '-0.001',
        ];

        foreach ($testCases as $input) {
            $native = bcfloor($input);
            $polyfill = Php84::bcfloor($input);
            Assert::same($native, $polyfill, "Parity failed for bcfloor('$input')");
        }
    }

    public function testBcfloorZeroAndEdgeCasesParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcfloor() not available');
            return;
        }

        $testCases = [
            '0',
            '0.0',
            '0.999999',
        ];

        foreach ($testCases as $input) {
            $native = bcfloor($input);
            $polyfill = Php84::bcfloor($input);
            Assert::same($native, $polyfill, "Parity failed for bcfloor('$input')");
        }
    }

    // =========================================================================
    // bcround Parity Tests - Default Mode (HalfAwayFromZero)
    // =========================================================================

    public function testBcroundDefaultModePositiveParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            '1.4',
            '1.49',
            '1.5',
            '1.51',
            '1.6',
            '1.9',
        ];

        foreach ($testCases as $input) {
            $native = bcround($input);
            $polyfill = Php84::bcround($input);
            Assert::same($native, $polyfill, "Parity failed for bcround('$input')");
        }
    }

    public function testBcroundDefaultModeNegativeParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            '-1.4',
            '-1.49',
            '-1.5',
            '-1.51',
            '-1.6',
        ];

        foreach ($testCases as $input) {
            $native = bcround($input);
            $polyfill = Php84::bcround($input);
            Assert::same($native, $polyfill, "Parity failed for bcround('$input')");
        }
    }

    public function testBcroundWithPrecisionParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['1.15', 1],
            ['1.24', 1],
            ['1.25', 1],
            ['1.234', 2],
            ['1.235', 2],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1]);
            $polyfill = Php84::bcround($args[0], $args[1]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]})");
        }
    }

    // =========================================================================
    // bcround Parity Tests - HalfTowardsZero Mode
    // =========================================================================

    public function testBcroundHalfTowardsZeroPositiveParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['1.4', 0, RoundingMode::HalfTowardsZero],
            ['1.49', 0, RoundingMode::HalfTowardsZero],
            ['1.5', 0, RoundingMode::HalfTowardsZero],
            ['1.51', 0, RoundingMode::HalfTowardsZero],
            ['1.6', 0, RoundingMode::HalfTowardsZero],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, HalfTowardsZero)");
        }
    }

    public function testBcroundHalfTowardsZeroNegativeParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['-1.4', 0, RoundingMode::HalfTowardsZero],
            ['-1.49', 0, RoundingMode::HalfTowardsZero],
            ['-1.5', 0, RoundingMode::HalfTowardsZero],
            ['-1.51', 0, RoundingMode::HalfTowardsZero],
            ['-1.6', 0, RoundingMode::HalfTowardsZero],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, HalfTowardsZero)");
        }
    }

    // =========================================================================
    // bcround Parity Tests - HalfEven Mode (Banker's Rounding)
    // =========================================================================

    public function testBcroundHalfEvenPositiveParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['1.4', 0, RoundingMode::HalfEven],
            ['1.49', 0, RoundingMode::HalfEven],
            ['1.5', 0, RoundingMode::HalfEven],
            ['2.5', 0, RoundingMode::HalfEven],
            ['3.5', 0, RoundingMode::HalfEven],
            ['4.5', 0, RoundingMode::HalfEven],
            ['1.51', 0, RoundingMode::HalfEven],
            ['1.6', 0, RoundingMode::HalfEven],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, HalfEven)");
        }
    }

    public function testBcroundHalfEvenNegativeParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['-1.4', 0, RoundingMode::HalfEven],
            ['-1.5', 0, RoundingMode::HalfEven],
            ['-2.5', 0, RoundingMode::HalfEven],
            ['-1.51', 0, RoundingMode::HalfEven],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, HalfEven)");
        }
    }

    // =========================================================================
    // bcround Parity Tests - HalfOdd Mode
    // =========================================================================

    public function testBcroundHalfOddPositiveParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['1.4', 0, RoundingMode::HalfOdd],
            ['1.5', 0, RoundingMode::HalfOdd],
            ['2.5', 0, RoundingMode::HalfOdd],
            ['3.5', 0, RoundingMode::HalfOdd],
            ['4.5', 0, RoundingMode::HalfOdd],
            ['1.51', 0, RoundingMode::HalfOdd],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, HalfOdd)");
        }
    }

    public function testBcroundHalfOddNegativeParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['-1.4', 0, RoundingMode::HalfOdd],
            ['-1.5', 0, RoundingMode::HalfOdd],
            ['-2.5', 0, RoundingMode::HalfOdd],
            ['-1.51', 0, RoundingMode::HalfOdd],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, HalfOdd)");
        }
    }

    // =========================================================================
    // bcround Parity Tests - PositiveInfinity Mode (Ceiling)
    // =========================================================================

    public function testBcroundPositiveInfinityParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['1.1', 0, RoundingMode::PositiveInfinity],
            ['1.5', 0, RoundingMode::PositiveInfinity],
            ['1.9', 0, RoundingMode::PositiveInfinity],
            ['1.0', 0, RoundingMode::PositiveInfinity],
            ['-0.1', 0, RoundingMode::PositiveInfinity],
            ['-1.0', 0, RoundingMode::PositiveInfinity],
            ['-1.5', 0, RoundingMode::PositiveInfinity],
            ['-1.9', 0, RoundingMode::PositiveInfinity],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, PositiveInfinity)");
        }
    }

    // =========================================================================
    // bcround Parity Tests - NegativeInfinity Mode (Floor)
    // =========================================================================

    public function testBcroundNegativeInfinityParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['1.0', 0, RoundingMode::NegativeInfinity],
            ['1.1', 0, RoundingMode::NegativeInfinity],
            ['1.5', 0, RoundingMode::NegativeInfinity],
            ['1.9', 0, RoundingMode::NegativeInfinity],
            ['-0.1', 0, RoundingMode::NegativeInfinity],
            ['-1.1', 0, RoundingMode::NegativeInfinity],
            ['-1.5', 0, RoundingMode::NegativeInfinity],
            ['-1.9', 0, RoundingMode::NegativeInfinity],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, NegativeInfinity)");
        }
    }

    // =========================================================================
    // bcround Parity Tests - TowardsZero Mode (Truncate)
    // =========================================================================

    public function testBcroundTowardsZeroParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['1.0', 0, RoundingMode::TowardsZero],
            ['1.1', 0, RoundingMode::TowardsZero],
            ['1.5', 0, RoundingMode::TowardsZero],
            ['1.9', 0, RoundingMode::TowardsZero],
            ['-1.0', 0, RoundingMode::TowardsZero],
            ['-1.1', 0, RoundingMode::TowardsZero],
            ['-1.5', 0, RoundingMode::TowardsZero],
            ['-1.9', 0, RoundingMode::TowardsZero],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, TowardsZero)");
        }
    }

    // =========================================================================
    // bcround Parity Tests - AwayFromZero Mode
    // =========================================================================

    public function testBcroundAwayFromZeroParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');
            return;
        }

        $testCases = [
            ['1.0', 0, RoundingMode::AwayFromZero],
            ['1.1', 0, RoundingMode::AwayFromZero],
            ['1.5', 0, RoundingMode::AwayFromZero],
            ['1.9', 0, RoundingMode::AwayFromZero],
            ['-1.0', 0, RoundingMode::AwayFromZero],
            ['-1.1', 0, RoundingMode::AwayFromZero],
            ['-1.5', 0, RoundingMode::AwayFromZero],
            ['-1.9', 0, RoundingMode::AwayFromZero],
        ];

        foreach ($testCases as $args) {
            $native = bcround($args[0], $args[1], $args[2]);
            $polyfill = Php84::bcround($args[0], $args[1], $args[2]);
            Assert::same($native, $polyfill, "Parity failed for bcround('{$args[0]}', {$args[1]}, AwayFromZero)");
        }
    }
}

(new Php84ParityTest())->run();
