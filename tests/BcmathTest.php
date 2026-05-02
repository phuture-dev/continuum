<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Tester\{Assert, TestCase};
use Phuture\Continuum\Extension\Bcmath;

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for Bcmath polyfill methods.
 *
 * @testCase
 */
class BcmathTest extends TestCase
{
    // =========================================================================
    // bcceil Tests
    // =========================================================================

    public function testBcceilPositiveInteger(): void
    {
        Assert::same('3', Bcmath::bcceil('3'));
    }

    public function testBcceilPositiveDecimal(): void
    {
        Assert::same('4', Bcmath::bcceil('3.5'));
    }

    public function testBcceilNegativeDecimal(): void
    {
        Assert::same('-3', Bcmath::bcceil('-3.5'));
    }

    public function testBcceilZero(): void
    {
        Assert::same('0', Bcmath::bcceil('0'));
    }

    public function testBcceilNegativeInteger(): void
    {
        Assert::same('-5', Bcmath::bcceil('-5'));
    }

    public function testBcceilSmallPositiveDecimal(): void
    {
        Assert::same('1', Bcmath::bcceil('0.001'));
    }

    public function testBcceilSmallNegativeDecimal(): void
    {
        Assert::same('0', Bcmath::bcceil('-0.001'));
    }

    // =========================================================================
    // bcfloor Tests
    // =========================================================================

    public function testBcfloorPositiveInteger(): void
    {
        Assert::same('3', Bcmath::bcfloor('3'));
    }

    public function testBcfloorPositiveDecimal(): void
    {
        Assert::same('3', Bcmath::bcfloor('3.5'));
    }

    public function testBcfloorNegativeDecimal(): void
    {
        Assert::same('-4', Bcmath::bcfloor('-3.5'));
    }

    public function testBcfloorZero(): void
    {
        Assert::same('0', Bcmath::bcfloor('0'));
    }

    public function testBcfloorNegativeInteger(): void
    {
        Assert::same('-5', Bcmath::bcfloor('-5'));
    }

    public function testBcfloorSmallPositiveDecimal(): void
    {
        Assert::same('0', Bcmath::bcfloor('0.999'));
    }

    public function testBcfloorSmallNegativeDecimal(): void
    {
        Assert::same('-1', Bcmath::bcfloor('-0.001'));
    }

    // =========================================================================
    // bcround Tests
    // =========================================================================

    public function testBcroundPositiveUp(): void
    {
        Assert::same('4', Bcmath::bcround('3.5'));
    }

    public function testBcroundPositiveDown(): void
    {
        Assert::same('3', Bcmath::bcround('3.4'));
    }

    public function testBcroundNegativeUp(): void
    {
        Assert::same('-3', Bcmath::bcround('-3.4'));
    }

    public function testBcroundNegativeDown(): void
    {
        Assert::same('-4', Bcmath::bcround('-3.5'));
    }

    public function testBcroundZero(): void
    {
        Assert::same('0', Bcmath::bcround('0'));
    }

    public function testBcroundWithPrecision(): void
    {
        Assert::same('3.14', Bcmath::bcround('3.14159', 2));
    }

    public function testBcroundWithPrecisionZero(): void
    {
        Assert::same('4', Bcmath::bcround('3.5', 0));
    }

    public function testBcroundWithNegativePrecision(): void
    {
        Assert::same('0', Bcmath::bcround('3.5', -1));
    }

    // =========================================================================
    // bcdivmod Tests
    // =========================================================================

    public function testBcdivmodBasic(): void
    {
        $result = Bcmath::bcdivmod('10', '3');
        Assert::same('3', $result[0]);
        Assert::same('1', $result[1]);
    }

    public function testBcdivmodExactDivision(): void
    {
        $result = Bcmath::bcdivmod('10', '5');
        Assert::same('2', $result[0]);
        Assert::same('0', $result[1]);
    }

    public function testBcdivmodWithScale(): void
    {
        $result = Bcmath::bcdivmod('10', '3', 2);
        Assert::same('3', $result[0]);
        Assert::same('1.00', $result[1]);
    }

    public function testBcdivmodNegativeDividend(): void
    {
        $result = Bcmath::bcdivmod('-10', '3');
        Assert::same('-3', $result[0]);
        Assert::same('-1', $result[1]);
    }

    public function testBcdivmodLargeNumbers(): void
    {
        $result = Bcmath::bcdivmod('100000000000000000000', '3');
        Assert::same('33333333333333333333', $result[0]);
        Assert::same('1', $result[1]);
    }
}

(new BcmathTest())->run();
