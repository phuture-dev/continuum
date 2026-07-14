<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php86;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Php86 polyfill against native PHP 8.6+ functions.
 *
 * @testCase
 */
class Php86ParityTest extends TestCase
{
    private bool $hasNativeClamp;
    private bool $hasNativeGraphemeStrrev;

    protected function setUp(): void
    {
        $this->hasNativeClamp = function_exists('clamp');
        $this->hasNativeGraphemeStrrev = function_exists('grapheme_strrev');
    }

    // =========================================================================
    // clamp Parity Tests
    // =========================================================================

    public function testClampParity(): void
    {
        if (!$this->hasNativeClamp) {
            Assert::false($this->hasNativeClamp, 'Native clamp() not available');

            return;
        }

        $testCases = [
            [5, 1, 10],
            [1, 1, 10],
            [10, 1, 10],
            [-5, 1, 10],
            [42, 1, 10],
            [2.5, 1.0, 5.0],
            [0.5, 1.0, 5.0],
            [9.9, 1.0, 5.0],
            [3, 3, 3],
            [5, 3, 3],
            [7, 1.5, 9.5],
        ];

        foreach ($testCases as [$value, $min, $max]) {
            Assert::same(
                clamp($value, $min, $max),
                Php86::clamp($value, $min, $max),
                "Parity failed for clamp({$value}, {$min}, {$max})"
            );
        }
    }

    public function testClampExceptionParity(): void
    {
        if (!$this->hasNativeClamp) {
            Assert::false($this->hasNativeClamp, 'Native clamp() not available');

            return;
        }

        $errorCases = [
            [1.0, NAN, 10.0],
            [1.0, 0.0, NAN],
            [5, 10, 1],
        ];

        foreach ($errorCases as [$value, $min, $max]) {
            $native = Assert::exception(function () use ($value, $min, $max) {
                clamp($value, $min, $max);
            }, \ValueError::class);

            $polyfill = Assert::exception(function () use ($value, $min, $max) {
                Php86::clamp($value, $min, $max);
            }, \ValueError::class);

            Assert::same($native->getMessage(), $polyfill->getMessage());
        }
    }

    // =========================================================================
    // grapheme_strrev Parity Tests
    // =========================================================================

    public function testGraphemeStrrevParity(): void
    {
        if (!$this->hasNativeGraphemeStrrev) {
            Assert::false($this->hasNativeGraphemeStrrev, 'Native grapheme_strrev() not available');

            return;
        }

        $testCases = [
            '',
            'a',
            'ABCDE',
            "cafe\u{0301}",
            'Apple🍏',
            '🇨🇳 - 🇳🇨',
            'Hello, 世界',
        ];

        foreach ($testCases as $string) {
            Assert::same(
                grapheme_strrev($string),
                Php86::grapheme_strrev($string),
                "Parity failed for grapheme_strrev('{$string}')"
            );
        }
    }
}

(new Php86ParityTest())->run();
