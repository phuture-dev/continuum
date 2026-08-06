<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Error;
use Exception;
use ValueError;
use ReflectionClass;
use Tester\{Assert, TestCase};
use Time\{Duration, TimeException};
use Phuture\Continuum\Time\DurationMath;

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for the Time\Duration class behaviour.
 *
 * These tests exercise the public API of the Duration class shell and must
 * pass against both the polyfill and any future native implementation. Where
 * an assertion pins a project-specific guess rather than an RFC guarantee,
 * it is guarded by a ReflectionClass::isUserDefined() skip so the CI job
 * running PHP 8.6 stays green the day the native class ships.
 *
 * @testCase
 */
class DurationTest extends TestCase
{
    /**
     * Reports whether the loaded Duration class is user-defined.
     *
     * Used to guard assertions that pin project-specific choices (exact
     * ValueError message texts, the ISO-8601 exception type) that may
     * diverge from the eventual native implementation.
     */
    private function isUserDefined(): bool
    {
        return (new ReflectionClass(Duration::class))->isUserDefined();
    }

    // =========================================================================
    // Existence and type hierarchy
    // =========================================================================

    public function testDurationClassExists(): void
    {
        Assert::true(class_exists(Duration::class));
    }

    public function testTimeExceptionClassExists(): void
    {
        Assert::true(class_exists(TimeException::class));
    }

    public function testTimeExceptionIsAnException(): void
    {
        Assert::true(is_a(TimeException::class, Exception::class, true));
    }

    // =========================================================================
    // Constructor
    // =========================================================================

    public function testPrivateConstructorRaisesError(): void
    {
        Assert::exception(function () {
            new Duration(1, 0, false);
        }, Error::class);
    }

    // =========================================================================
    // Property values
    // =========================================================================

    public function testFromSecondsPropertyValues(): void
    {
        $d = Duration::fromSeconds(3, 500_000_000);
        Assert::same(3, $d->seconds);
        Assert::same(500_000_000, $d->nanoseconds);
        Assert::false($d->negative);
    }

    public function testNegatePropertyValues(): void
    {
        $d = Duration::fromSeconds(5)->negate();
        Assert::same(5, $d->seconds);
        Assert::same(0, $d->nanoseconds);
        Assert::true($d->negative);
    }

    public function testZeroIsNeverNegative(): void
    {
        $d = Duration::fromSeconds(0)->negate();
        Assert::false($d->negative);
    }

    // =========================================================================
    // Comparison battery (validates the hidden-property trick)
    // =========================================================================

    public function testNegativeLessThanPositive(): void
    {
        $neg1h = Duration::fromHours(1)->negate();
        $pos1s = Duration::fromSeconds(1);
        Assert::true($neg1h < $pos1s);
        Assert::false($neg1h > $pos1s);
        Assert::true($neg1h <= $pos1s);
        Assert::true($pos1s >= $neg1h);
        Assert::same(-1, $neg1h <=> $pos1s);
    }

    public function testEqualInstancesComparison(): void
    {
        $a = Duration::fromSeconds(5, 100);
        $b = Duration::fromSeconds(5, 100);
        Assert::true($a == $b);
        Assert::false($a < $b);
        Assert::same(0, $a <=> $b);
        Assert::false($a === $b);
    }

    public function testNegativeNanosecondOrdering(): void
    {
        $more = Duration::fromSeconds(0, 300)->negate();
        $less = Duration::fromSeconds(0, 200)->negate();
        Assert::true($more < $less);
        Assert::same(-1, $more <=> $less);
    }

    public function testRfcSortExampleViaSpaceship(): void
    {
        $durations = [
            Duration::fromSeconds(1),
            Duration::fromHours(1)->negate(),
            Duration::fromSeconds(1, 500_000_000),
            Duration::fromMilliseconds(500),
        ];

        usort($durations, function ($a, $b) {
            return $a <=> $b;
        });

        Assert::same(-3600, $durations[0]->negative ? -$durations[0]->seconds : $durations[0]->seconds);
        Assert::false($durations[1]->negative);
        Assert::same(0, $durations[1]->seconds);
        Assert::same(500_000_000, $durations[1]->nanoseconds);
        Assert::same(1, $durations[2]->seconds);
        Assert::same(1, $durations[3]->seconds);
        Assert::same(500_000_000, $durations[3]->nanoseconds);
    }

    public function testRfcSortExampleViaDurationCompare(): void
    {
        $durations = [
            Duration::fromSeconds(1),
            Duration::fromHours(1)->negate(),
            Duration::fromSeconds(1, 500_000_000),
            Duration::fromMilliseconds(500),
        ];

        usort($durations, [Duration::class, 'compare']);

        Assert::true($durations[0]->negative);
        Assert::same(3600, $durations[0]->seconds);
        Assert::same(500_000_000, $durations[1]->nanoseconds);
        Assert::same(1, $durations[2]->seconds);
        Assert::same(1, $durations[3]->seconds);
        Assert::same(500_000_000, $durations[3]->nanoseconds);
    }

    // =========================================================================
    // Arithmetic through the class
    // =========================================================================

    public function testAdd(): void
    {
        $result = Duration::fromSeconds(0, 999_999_999)->add(Duration::fromSeconds(0, 1));
        Assert::same(1, $result->seconds);
        Assert::same(0, $result->nanoseconds);
    }

    public function testSub(): void
    {
        $result = Duration::fromSeconds(3)->sub(Duration::fromSeconds(1));
        Assert::same(2, $result->seconds);
        Assert::false($result->negative);
    }

    public function testSubYieldsNegative(): void
    {
        $result = Duration::fromSeconds(1)->sub(Duration::fromSeconds(2));
        Assert::same(1, $result->seconds);
        Assert::true($result->negative);
    }

    public function testMultiplyByRfcExample(): void
    {
        $result = Duration::fromMilliseconds(100)->multiplyBy(2 ** 5);
        Assert::same(3, $result->seconds);
        Assert::same(200_000_000, $result->nanoseconds);
    }

    public function testDivideByRfcExample(): void
    {
        $result = Duration::fromSeconds(1)->divideBy(2);
        Assert::same(0, $result->seconds);
        Assert::same(500_000_000, $result->nanoseconds);
    }

    public function testAbsolute(): void
    {
        $result = Duration::fromSeconds(5)->negate()->absolute();
        Assert::false($result->negative);
        Assert::same(5, $result->seconds);
    }

    // =========================================================================
    // ISO-8601
    // =========================================================================

    public function testFromIso8601(): void
    {
        $d = Duration::fromIso8601DurationString('PT1H30M15S');
        Assert::same(5415, $d->seconds);
    }

    public function testFromIso8601Invalid(): void
    {
        Assert::exception(function () {
            Duration::fromIso8601DurationString('invalid');
        }, TimeException::class);
    }

    // =========================================================================
    // Error type guarantees (RFC-pinned, asserted unconditionally)
    // =========================================================================

    public function testFromSecondsNegativeThrowsValueError(): void
    {
        Assert::exception(function () {
            Duration::fromSeconds(-1);
        }, ValueError::class);
    }

    public function testMultiplyByNegativeThrowsValueError(): void
    {
        Assert::exception(function () {
            Duration::fromSeconds(1)->multiplyBy(-1);
        }, ValueError::class);
    }

    public function testDivideByZeroThrowsValueError(): void
    {
        Assert::exception(function () {
            Duration::fromSeconds(1)->divideBy(0);
        }, ValueError::class);
    }

    public function testOverflowThrowsTimeException(): void
    {
        if (PHP_INT_SIZE < 8) {
            \Tester\Environment::skip('64-bit only test.');
        }

        Assert::exception(function () {
            Duration::fromSeconds(DurationMath::maximumSeconds())->add(Duration::fromSeconds(1));
        }, TimeException::class);
    }

    // =========================================================================
    // Pinned-guess assertions (guarded by isUserDefined)
    // =========================================================================

    public function testFromSecondsValueErrorMessage(): void
    {
        if (!$this->isUserDefined()) {
            \Tester\Environment::skip('Native Time\Duration is loaded; message parity is not asserted.');
        }

        $exception = Assert::exception(function () {
            Duration::fromSeconds(-1);
        }, ValueError::class);
        Assert::contains('must not be negative', $exception->getMessage());
    }

    public function testIso8601ExceptionType(): void
    {
        if (!$this->isUserDefined()) {
            \Tester\Environment::skip('Native Time\Duration is loaded; ISO exception type parity is not asserted.');
        }

        Assert::exception(function () {
            Duration::fromIso8601DurationString('invalid');
        }, TimeException::class);
    }

    // =========================================================================
    // Immutability
    // =========================================================================

    public function testReadonlyEnforcedOn81Plus(): void
    {
        if (PHP_VERSION_ID < 80100) {
            \Tester\Environment::skip('readonly is only enforced on PHP 8.1+.');
        }

        $d = Duration::fromSeconds(1);
        Assert::exception(function () use ($d) {
            $d->seconds = 5;
        }, Error::class);
    }

    public function testReadonlyNotEnforcedOn80(): void
    {
        if (PHP_VERSION_ID >= 80100) {
            \Tester\Environment::skip('This test pins the documented non-enforcement on PHP 8.0.');
        }

        // On 8.0 writes succeed silently; this is the documented divergence.
        $d = Duration::fromSeconds(1);
        $d->seconds = 5;
        Assert::same(5, $d->seconds);
    }

    // =========================================================================
    // Surface
    // =========================================================================

    public function testVarDumpShowsThreeProperties(): void
    {
        $d = Duration::fromSeconds(1, 500_000_000);
        ob_start();
        var_dump($d);
        $output = ob_get_clean();

        Assert::contains('seconds', $output);
        Assert::contains('nanoseconds', $output);
        Assert::contains('negative', $output);
        Assert::false(strpos($output, 'comparable') !== false || strpos($output, 'comparabl') !== false);
    }

    public function testJsonEncodeHasThreeFields(): void
    {
        $d = Duration::fromSeconds(1, 500_000_000);
        $json = json_encode($d);
        $decoded = json_decode($json, true);

        Assert::same(1, $decoded['seconds']);
        Assert::same(500_000_000, $decoded['nanoseconds']);
        Assert::false($decoded['negative']);
        Assert::count(3, $decoded);
    }

    public function testGetObjectVarsHasThreeEntries(): void
    {
        $d = Duration::fromSeconds(1, 500_000_000);
        $vars = get_object_vars($d);

        Assert::count(3, $vars);
        Assert::true(array_key_exists('seconds', $vars));
        Assert::true(array_key_exists('nanoseconds', $vars));
        Assert::true(array_key_exists('negative', $vars));
    }

    public function testForeachIteratesThreeTimes(): void
    {
        $d = Duration::fromSeconds(1, 500_000_000);
        $count = 0;

        foreach ($d as $key => $value) {
            $count++;
        }

        Assert::same(3, $count);
    }

    public function testCloneIsEqual(): void
    {
        $d = Duration::fromSeconds(1, 500_000_000);
        $clone = clone $d;

        Assert::true($clone == $d);
        Assert::same(0, $clone <=> $d);
        Assert::false($clone === $d);
    }
}

(new DurationTest())->run();
