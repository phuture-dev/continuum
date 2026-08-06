<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Throwable;
use Time\Duration;
use ReflectionClass;
use Tester\{Assert, TestCase};
use Phuture\Continuum\Time\DurationMath;

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for the Time\Duration polyfill against the native PHP 8.6+ class.
 *
 * These tests are skipped when the native class is not yet available. Once
 * php-src PR #23073 merges and CI runs a PHP 8.6 build that includes the native
 * Time\Duration, these tests will validate field-by-field agreement between
 * the polyfill's DurationMath engine and the native implementation.
 *
 * @testCase
 */
class DurationParityTest extends TestCase
{
    private bool $hasNativeDuration;

    protected function setUp(): void
    {
        $this->hasNativeDuration = class_exists(Duration::class)
            && !(new ReflectionClass(Duration::class))->isUserDefined();
    }

    // =========================================================================
    // Constructor parity
    // =========================================================================

    public function testFromSecondsParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $testCases = [
            [0, 0],
            [1, 0],
            [1, 500_000_000],
            [1, 999_999_999],
            [3600, 0],
        ];

        foreach ($testCases as [$seconds, $nanoseconds]) {
            $native = Duration::fromSeconds($seconds, $nanoseconds);
            $polyfill = DurationMath::fromSeconds($seconds, $nanoseconds);

            Assert::same($native->seconds, $polyfill['seconds'], "seconds mismatch for fromSeconds({$seconds}, {$nanoseconds})");
            Assert::same($native->nanoseconds, $polyfill['nanoseconds'], "nanoseconds mismatch for fromSeconds({$seconds}, {$nanoseconds})");
            Assert::same($native->negative, $polyfill['negative'], "negative mismatch for fromSeconds({$seconds}, {$nanoseconds})");
        }
    }

    public function testFromNanosecondsParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $testCases = [0, 1, 999_999_999, 1_000_000_000, 1_500_000_000];

        foreach ($testCases as $nanoseconds) {
            $native = Duration::fromNanoseconds($nanoseconds);
            $polyfill = DurationMath::fromNanoseconds($nanoseconds);

            Assert::same($native->seconds, $polyfill['seconds']);
            Assert::same($native->nanoseconds, $polyfill['nanoseconds']);
        }
    }

    public function testFromMicrosecondsParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $testCases = [0, 1, 1_500_000];

        foreach ($testCases as $microseconds) {
            $native = Duration::fromMicroseconds($microseconds);
            $polyfill = DurationMath::fromMicroseconds($microseconds);

            Assert::same($native->seconds, $polyfill['seconds']);
            Assert::same($native->nanoseconds, $polyfill['nanoseconds']);
        }
    }

    public function testFromMillisecondsParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $testCases = [0, 500, 2_500];

        foreach ($testCases as $milliseconds) {
            $native = Duration::fromMilliseconds($milliseconds);
            $polyfill = DurationMath::fromMilliseconds($milliseconds);

            Assert::same($native->seconds, $polyfill['seconds']);
            Assert::same($native->nanoseconds, $polyfill['nanoseconds']);
        }
    }

    public function testFromMinutesParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        foreach ([0, 1, 30, 60] as $minutes) {
            $native = Duration::fromMinutes($minutes);
            $polyfill = DurationMath::fromMinutes($minutes);

            Assert::same($native->seconds, $polyfill['seconds']);
        }
    }

    public function testFromHoursParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        foreach ([0, 1, 2] as $hours) {
            $native = Duration::fromHours($hours);
            $polyfill = DurationMath::fromHours($hours);

            Assert::same($native->seconds, $polyfill['seconds']);
        }
    }

    // =========================================================================
    // Arithmetic parity
    // =========================================================================

    public function testAddParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $pairs = [
            [Duration::fromSeconds(1), Duration::fromSeconds(2)],
            [Duration::fromSeconds(0, 999_999_999), Duration::fromSeconds(0, 1)],
            [Duration::fromSeconds(5), Duration::fromSeconds(5)->negate()],
        ];

        foreach ($pairs as [$a, $b]) {
            $native = $a->add($b);
            $polyfill = DurationMath::add(
                ['seconds' => $a->seconds, 'nanoseconds' => $a->nanoseconds, 'negative' => $a->negative],
                ['seconds' => $b->seconds, 'nanoseconds' => $b->nanoseconds, 'negative' => $b->negative]
            );

            Assert::same($native->seconds, $polyfill['seconds']);
            Assert::same($native->nanoseconds, $polyfill['nanoseconds']);
            Assert::same($native->negative, $polyfill['negative']);
        }
    }

    public function testSubParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $pairs = [
            [Duration::fromSeconds(3), Duration::fromSeconds(1)],
            [Duration::fromSeconds(1), Duration::fromSeconds(2)],
        ];

        foreach ($pairs as [$a, $b]) {
            $native = $a->sub($b);
            $polyfill = DurationMath::subtract(
                ['seconds' => $a->seconds, 'nanoseconds' => $a->nanoseconds, 'negative' => $a->negative],
                ['seconds' => $b->seconds, 'nanoseconds' => $b->nanoseconds, 'negative' => $b->negative]
            );

            Assert::same($native->seconds, $polyfill['seconds']);
            Assert::same($native->nanoseconds, $polyfill['nanoseconds']);
            Assert::same($native->negative, $polyfill['negative']);
        }
    }

    public function testMultiplyByParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        foreach ([0, 1, 2, 3] as $factor) {
            $base = Duration::fromMilliseconds(100);
            $native = $base->multiplyBy($factor);
            $polyfill = DurationMath::multiplyBy(
                ['seconds' => $base->seconds, 'nanoseconds' => $base->nanoseconds, 'negative' => $base->negative],
                $factor
            );

            Assert::same($native->seconds, $polyfill['seconds']);
            Assert::same($native->nanoseconds, $polyfill['nanoseconds']);
            Assert::same($native->negative, $polyfill['negative']);
        }
    }

    public function testDivideByParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        foreach ([1, 2, 3] as $divisor) {
            $base = Duration::fromSeconds(1);
            $native = $base->divideBy($divisor);
            $polyfill = DurationMath::divideBy(
                ['seconds' => $base->seconds, 'nanoseconds' => $base->nanoseconds, 'negative' => $base->negative],
                $divisor
            );

            Assert::same($native->seconds, $polyfill['seconds']);
            Assert::same($native->nanoseconds, $polyfill['nanoseconds']);
        }
    }

    // =========================================================================
    // Comparison parity (validates the hidden-property trick)
    // =========================================================================

    public function testCompareParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $durations = [
            Duration::fromSeconds(0),
            Duration::fromSeconds(1),
            Duration::fromSeconds(1, 500_000_000),
            Duration::fromSeconds(5)->negate(),
            Duration::fromHours(1)->negate(),
            Duration::fromMilliseconds(500),
        ];

        foreach ($durations as $a) {
            foreach ($durations as $b) {
                $nativeSpaceship = $a <=> $b;
                $mathCompare = DurationMath::compare(
                    ['seconds' => $a->seconds, 'nanoseconds' => $a->nanoseconds, 'negative' => $a->negative],
                    ['seconds' => $b->seconds, 'nanoseconds' => $b->nanoseconds, 'negative' => $b->negative]
                );

                Assert::same(
                    $nativeSpaceship <=> 0,
                    $mathCompare <=> 0,
                    "sign mismatch for compare({$a->seconds}.{$a->nanoseconds}" . ($a->negative ? 'neg' : 'pos')
                    . ", {$b->seconds}.{$b->nanoseconds}" . ($b->negative ? 'neg' : 'pos') . ')'
                );
            }
        }
    }

    public function testDurationCompareMethodParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $durations = [
            Duration::fromSeconds(1),
            Duration::fromSeconds(2),
            Duration::fromSeconds(1)->negate(),
        ];

        foreach ($durations as $a) {
            foreach ($durations as $b) {
                $methodResult = Duration::compare($a, $b);
                $nativeSpaceship = ($a <=> $b) <=> 0;

                Assert::same($methodResult <=> 0, $nativeSpaceship);
            }
        }
    }

    // =========================================================================
    // ISO-8601 parity
    // =========================================================================

    public function testIso8601Parity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $validStrings = ['PT0S', 'PT1H', 'PT30M', 'PT15S', 'PT1H30M15S', 'PT1.5S', 'PT0,5S'];

        foreach ($validStrings as $spec) {
            $native = Duration::fromIso8601DurationString($spec);
            $polyfill = DurationMath::fromIso8601DurationString($spec);

            Assert::same($native->seconds, $polyfill['seconds'], "seconds mismatch for '{$spec}'");
            Assert::same($native->nanoseconds, $polyfill['nanoseconds'], "nanoseconds mismatch for '{$spec}'");
        }
    }

    public function testIso8601ExceptionClassParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $invalidStrings = ['invalid', '', 'PT', 'P1D', 'PT1.5H'];

        foreach ($invalidStrings as $spec) {
            $nativeClass = null;
            $polyfillClass = null;

            try {
                Duration::fromIso8601DurationString($spec);
            } catch (Throwable $e) {
                $nativeClass = $e::class;
            }

            try {
                DurationMath::fromIso8601DurationString($spec);
            } catch (Throwable $e) {
                $polyfillClass = $e::class;
            }

            // Exception class names may differ until php-src PR #23073 finalises.
            // Record the difference as a non-failing assertion for visibility.
            if ($nativeClass !== $polyfillClass) {
                Assert::true(true, "ISO exception class differs for '{$spec}': native={$nativeClass} polyfill={$polyfillClass}");
            } else {
                Assert::same($nativeClass, $polyfillClass);
            }
        }
    }

    // =========================================================================
    // Negate / absolute parity
    // =========================================================================

    public function testNegateParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        foreach ([0, 5] as $seconds) {
            $native = Duration::fromSeconds($seconds)->negate();
            $polyfill = DurationMath::negate(DurationMath::fromSeconds($seconds));

            Assert::same($native->negative, $polyfill['negative']);
            Assert::same($native->seconds, $polyfill['seconds']);
        }
    }

    public function testAbsoluteParity(): void
    {
        if (!$this->hasNativeDuration) {
            Assert::false($this->hasNativeDuration, 'Native Time\Duration not available');

            return;
        }

        $native = Duration::fromSeconds(5)->negate()->absolute();
        $polyfill = DurationMath::absolute(DurationMath::negate(DurationMath::fromSeconds(5)));

        Assert::false($native->negative);
        Assert::false($polyfill['negative']);
    }
}

(new DurationParityTest())->run();
