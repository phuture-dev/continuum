<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Tester\{Assert, TestCase};
use Phuture\Continuum\Extension\Bcmath;

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Bcmath polyfill against native bcmath functions.
 *
 * @testCase
 */
class BcmathParityTest extends TestCase
{
    private bool $hasNativeBcmath;

    protected function setUp(): void
    {
        $this->hasNativeBcmath = extension_loaded('bcmath') && \PHP_VERSION_ID >= 80400;
    }

    // =========================================================================
    // bcceil Parity Tests
    // =========================================================================

    public function testBcceilParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcceil() not available');

            return;
        }

        $testCases = [
            '3',
            '3.5',
            '-3.5',
            '0',
            '-5',
            '0.001',
            '-0.001',
            '100.999',
            '-100.001',
        ];

        foreach ($testCases as $num) {
            $native = bcceil($num);
            $polyfill = Bcmath::bcceil($num);

            Assert::same($native, $polyfill, "Parity failed for bcceil('{$num}')");
        }
    }

    // =========================================================================
    // bcfloor Parity Tests
    // =========================================================================

    public function testBcfloorParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcfloor() not available');

            return;
        }

        $testCases = [
            '3',
            '3.5',
            '-3.5',
            '0',
            '-5',
            '0.999',
            '-0.001',
            '100.001',
            '-100.999',
        ];

        foreach ($testCases as $num) {
            $native = bcfloor($num);
            $polyfill = Bcmath::bcfloor($num);

            Assert::same($native, $polyfill, "Parity failed for bcfloor('{$num}')");
        }
    }

    // =========================================================================
    // bcround Parity Tests
    // =========================================================================

    public function testBcroundParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcround() not available');

            return;
        }

        $testCases = [
            ['3.5', 0],
            ['3.4', 0],
            ['-3.4', 0],
            ['-3.5', 0],
            ['0', 0],
            ['3.14159', 2],
            ['3.14159', 4],
            ['100.999', 0],
            ['-100.999', 0],
        ];

        foreach ($testCases as [$num, $precision]) {
            $native = bcround($num, $precision);
            $polyfill = Bcmath::bcround($num, $precision);

            Assert::same($native, $polyfill, "Parity failed for bcround('{$num}', {$precision})");
        }
    }

    // =========================================================================
    // bcdivmod Parity Tests
    // =========================================================================

    public function testBcdivmodParity(): void
    {
        if (!$this->hasNativeBcmath) {
            Assert::false($this->hasNativeBcmath, 'Native bcdivmod() not available');

            return;
        }

        $testCases = [
            ['10', '3', null],
            ['10', '5', null],
            ['10', '3', 2],
            ['-10', '3', null],
            ['100000000000000000000', '3', null],
        ];

        foreach ($testCases as [$num1, $num2, $scale]) {
            $native = bcdivmod($num1, $num2, $scale);
            $polyfill = Bcmath::bcdivmod($num1, $num2, $scale);

            Assert::same($native[0], $polyfill[0], "Parity failed for bcdivmod('{$num1}', '{$num2}', " . var_export($scale, true) . ') quotient');
            Assert::same($native[1], $polyfill[1], "Parity failed for bcdivmod('{$num1}', '{$num2}', " . var_export($scale, true) . ') remainder');
        }
    }
}

(new BcmathParityTest())->run();
