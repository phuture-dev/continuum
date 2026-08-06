<?php

declare(strict_types=1);

namespace Phuture\Continuum\Time;

use ValueError;
use Time\TimeException;

/**
 * Pure-math engine for the Time\Duration polyfill.
 *
 * This class implements all validation, arithmetic and parsing for the
 * Time\Duration value object introduced in PHP 8.6. Every public method
 * operates on a plain array shape — `array{seconds: int, nanoseconds: int,
 * negative: bool}` — so that the two class tiers (PHP 8.0 and PHP 8.1+) can
 * be thin shells that delegate here. The arithmetic is written exactly once;
 * the shells only marshal arguments and repackage results.
 *
 * The magnitude (seconds and nanoseconds) is always stored non-negative and
 * the sign lives in the separate `negative` flag. Zero is never negative.
 *
 * @see https://wiki.php.net/rfc/duration_class
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class DurationMath
{
    /**
     * Number of nanoseconds in one second.
     *
     * @var int
     */
    public const NANOSECONDS_PER_SECOND = 1_000_000_000;

    /**
     * Number of nanoseconds in one microsecond.
     *
     * @var int
     */
    public const NANOSECONDS_PER_MICROSECOND = 1_000;

    /**
     * Number of nanoseconds in one millisecond.
     *
     * @var int
     */
    public const NANOSECONDS_PER_MILLISECOND = 1_000_000;

    /**
     * Number of microseconds in one second.
     *
     * @var int
     */
    public const MICROSECONDS_PER_SECOND = 1_000_000;

    /**
     * Number of milliseconds in one second.
     *
     * @var int
     */
    public const MILLISECONDS_PER_SECOND = 1_000;

    /**
     * Number of seconds in one minute.
     *
     * @var int
     */
    public const SECONDS_PER_MINUTE = 60;

    /**
     * Number of seconds in one hour.
     *
     * @var int
     */
    public const SECONDS_PER_HOUR = 3_600;

    /**
     * Overflow message shared by every operation that exceeds the representable range.
     *
     * The digits are kept inside a string so that no integer literal in this file
     * exceeds 2_147_483_647, which would silently promote to float on a 32-bit build.
     *
     * @var string
     */
    private const OVERFLOW_MESSAGE
        = 'The maximum representable range is 9223372035999999999 nanoseconds (roughly 292 years)';

    /**
     * ISO-8601 duration grammar (no date components, largest unit is hours).
     *
     * Fractions are permitted only on the seconds component. The pattern matches
     * the empty 'PT' string, so an explicit "at least one component participated"
     * check is required after matching.
     *
     * @var string
     */
    private const ISO_8601_PATTERN = '/^PT(?:([0-9]+)H)?(?:([0-9]+)M)?(?:([0-9]+)(?:[.,]([0-9]{1,9}))?S)?$/D';

    /**
     * Reports whether the current build uses 64-bit integers.
     *
     * Encapsulated as a method because PHPStan resolves PHP_INT_SIZE to 8 and
     * would flag an inline comparison as unreachable dead code at level 6.
     *
     * @return bool True when PHP_INT_SIZE is at least 8 (64-bit), false otherwise
     */
    private static function supportsWideIntegers(): bool
    {
        return \PHP_INT_SIZE >= 8;
    }

    /**
     * Returns the maximum value allowed for the seconds component.
     *
     * On 64-bit builds this is 9_223_372_035, chosen so that
     * seconds * 10^9 + nanoseconds fits in int64. On 32-bit builds the cap
     * drops to PHP_INT_MAX (2_147_483_647), because the public int $seconds
     * property must be able to hold it.
     *
     * @return int The maximum number of whole seconds a Duration can represent
     */
    public static function maximumSeconds(): int
    {
        return self::supportsWideIntegers()
            ? intdiv(\PHP_INT_MAX - (self::NANOSECONDS_PER_SECOND - 1), self::NANOSECONDS_PER_SECOND)
            : \PHP_INT_MAX;
    }

    // =========================================================================
    // Construction
    // =========================================================================

    /**
     * Validates and normalises a magnitude triple into a Duration shape.
     *
     * Ensures seconds and nanoseconds are non-negative, nanoseconds is below
     * 10^9, seconds does not exceed maximumSeconds(), and the negative flag is
     * cleared when the magnitude is zero.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $d = DurationMath::create(1, 500_000_000, false);
     * // ['seconds' => 1, 'nanoseconds' => 500_000_000, 'negative' => false]
     * ```
     *
 * @param int $seconds The whole-second part of the magnitude (must be >= 0)
 * @param int $nanoseconds The sub-second part of the magnitude (0 to 999_999_999)
 * @param bool $negative Whether the duration is negative
     * @return array{seconds: int, nanoseconds: int, negative: bool} The normalised Duration shape
 * @throws ValueError If seconds or nanoseconds is negative, or nanoseconds exceeds 999_999_999
 * @throws TimeException If seconds exceeds maximumSeconds()
     */
    public static function create(int $seconds, int $nanoseconds, bool $negative): array
    {
        if ($seconds < 0) {
            throw new ValueError('Time\Duration::fromSeconds(): Argument #1 ($seconds) must not be negative');
        }

        if ($nanoseconds < 0) {
            throw new ValueError('Time\Duration::fromSeconds(): Argument #2 ($nanoseconds) must not be negative');
        }

        if ($nanoseconds > self::NANOSECONDS_PER_SECOND - 1) {
            throw new ValueError(
                'Time\Duration::fromSeconds(): Argument #2 ($nanoseconds) must be less than 1000000000'
            );
        }

        if ($seconds > self::maximumSeconds()) {
            throw new TimeException(self::OVERFLOW_MESSAGE);
        }

        if ($seconds === 0 && $nanoseconds === 0) {
            $negative = false;
        }

        return ['seconds' => $seconds, 'nanoseconds' => $nanoseconds, 'negative' => $negative];
    }

    /**
     * Builds a Duration from whole seconds and an optional nanosecond remainder.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $d = DurationMath::fromSeconds(3, 500_000_000);
     * // 3.5 seconds
     * ```
     *
 * @param int $seconds The whole-second part of the magnitude (must be >= 0)
 * @param int $nanoseconds The sub-second remainder (0 to 999_999_999, default: 0)
     * @return array{seconds: int, nanoseconds: int, negative: bool} The Duration shape
 * @throws ValueError If seconds or nanoseconds is negative, or nanoseconds is out of range
 * @throws TimeException If the result exceeds the representable range
     */
    public static function fromSeconds(int $seconds, int $nanoseconds = 0): array
    {
        return self::create($seconds, $nanoseconds, false);
    }

    /**
     * Builds a Duration from a total number of nanoseconds.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $d = DurationMath::fromNanoseconds(1_500_000_000);
     * // 1 second and 500_000_000 nanoseconds
     * ```
     *
 * @param int $nanoseconds The total number of nanoseconds (must be >= 0)
     * @return array{seconds: int, nanoseconds: int, negative: bool} The Duration shape
 * @throws ValueError If nanoseconds is negative
 * @throws TimeException If the result exceeds the representable range
     */
    public static function fromNanoseconds(int $nanoseconds): array
    {
        if ($nanoseconds < 0) {
            throw new ValueError('Time\Duration::fromNanoseconds(): Argument #1 ($nanoseconds) must not be negative');
        }

        $seconds = intdiv($nanoseconds, self::NANOSECONDS_PER_SECOND);
        $remainder = $nanoseconds % self::NANOSECONDS_PER_SECOND;

        return self::create($seconds, $remainder, false);
    }

    /**
     * Builds a Duration from a total number of microseconds.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $d = DurationMath::fromMicroseconds(1_500_000);
     * // 1 second and 500_000_000 nanoseconds
     * ```
     *
 * @param int $microseconds The total number of microseconds (must be >= 0)
     * @return array{seconds: int, nanoseconds: int, negative: bool} The Duration shape
 * @throws ValueError If microseconds is negative
 * @throws TimeException If the result exceeds the representable range
     */
    public static function fromMicroseconds(int $microseconds): array
    {
        if ($microseconds < 0) {
            throw new ValueError('Time\Duration::fromMicroseconds(): Argument #1 ($microseconds) must not be negative');
        }

        $seconds = intdiv($microseconds, self::MICROSECONDS_PER_SECOND);
        $remainder = ($microseconds % self::MICROSECONDS_PER_SECOND) * self::NANOSECONDS_PER_MICROSECOND;

        return self::create($seconds, $remainder, false);
    }

    /**
     * Builds a Duration from a total number of milliseconds.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $d = DurationMath::fromMilliseconds(500);
     * // 0 seconds and 500_000_000 nanoseconds
     * ```
     *
 * @param int $milliseconds The total number of milliseconds (must be >= 0)
     * @return array{seconds: int, nanoseconds: int, negative: bool} The Duration shape
 * @throws ValueError If milliseconds is negative
 * @throws TimeException If the result exceeds the representable range
     */
    public static function fromMilliseconds(int $milliseconds): array
    {
        if ($milliseconds < 0) {
            throw new ValueError('Time\Duration::fromMilliseconds(): Argument #1 ($milliseconds) must not be negative');
        }

        $seconds = intdiv($milliseconds, self::MILLISECONDS_PER_SECOND);
        $remainder = ($milliseconds % self::MILLISECONDS_PER_SECOND) * self::NANOSECONDS_PER_MILLISECOND;

        return self::create($seconds, $remainder, false);
    }

    /**
     * Builds a Duration from a number of whole minutes.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $d = DurationMath::fromMinutes(2);
     * // 120 seconds
     * ```
     *
 * @param int $minutes The number of minutes (must be >= 0)
     * @return array{seconds: int, nanoseconds: int, negative: bool} The Duration shape
 * @throws ValueError If minutes is negative
 * @throws TimeException If the result exceeds the representable range
     */
    public static function fromMinutes(int $minutes): array
    {
        if ($minutes < 0) {
            throw new ValueError('Time\Duration::fromMinutes(): Argument #1 ($minutes) must not be negative');
        }

        if ($minutes > intdiv(self::maximumSeconds(), self::SECONDS_PER_MINUTE)) {
            throw new TimeException(self::OVERFLOW_MESSAGE);
        }

        return self::create($minutes * self::SECONDS_PER_MINUTE, 0, false);
    }

    /**
     * Builds a Duration from a number of whole hours.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $d = DurationMath::fromHours(2);
     * // 7200 seconds
     * ```
     *
 * @param int $hours The number of hours (must be >= 0)
     * @return array{seconds: int, nanoseconds: int, negative: bool} The Duration shape
 * @throws ValueError If hours is negative
 * @throws TimeException If the result exceeds the representable range
     */
    public static function fromHours(int $hours): array
    {
        if ($hours < 0) {
            throw new ValueError('Time\Duration::fromHours(): Argument #1 ($hours) must not be negative');
        }

        if ($hours > intdiv(self::maximumSeconds(), self::SECONDS_PER_HOUR)) {
            throw new TimeException(self::OVERFLOW_MESSAGE);
        }

        return self::create($hours * self::SECONDS_PER_HOUR, 0, false);
    }

    // =========================================================================
    // Sign operations
    // =========================================================================

    /**
     * Returns the negation of a Duration, flipping the sign of the magnitude.
     *
     * A zero Duration is never negative: negating zero yields a non-negative zero.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $negated = DurationMath::negate(DurationMath::fromSeconds(5));
     * // negative 5 seconds
     * ```
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The Duration to negate
     * @return array{seconds: int, nanoseconds: int, negative: bool} The negated Duration
     */
    public static function negate(array $duration): array
    {
        return [
            'seconds' => $duration['seconds'],
            'nanoseconds' => $duration['nanoseconds'],
            'negative' => self::isZero($duration) ? false : !$duration['negative'],
        ];
    }

    /**
     * Returns the absolute value of a Duration, clearing the negative flag.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $abs = DurationMath::absolute(DurationMath::fromSeconds(5));
     * $abs = DurationMath::absolute($negated);
     * // both are positive 5 seconds
     * ```
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The Duration to make absolute
     * @return array{seconds: int, nanoseconds: int, negative: bool} The non-negative Duration
     */
    public static function absolute(array $duration): array
    {
        return [
            'seconds' => $duration['seconds'],
            'nanoseconds' => $duration['nanoseconds'],
            'negative' => false,
        ];
    }

    // =========================================================================
    // Arithmetic
    // =========================================================================

    /**
     * Adds two Durations, returning a new Duration with the sum.
     *
     * When both operands share the same sign, magnitudes add and a carry is
     * propagated from the nanosecond field. When signs differ, the smaller
     * magnitude is subtracted from the larger and the sign of the larger wins.
     * Equal magnitudes with opposite signs produce a non-negative zero.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $sum = DurationMath::add(
     *     DurationMath::fromSeconds(0, 999_999_999),
     *     DurationMath::fromSeconds(0, 1)
     * );
     * // 1 second, 0 nanoseconds
     * ```
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The first addend
 * @param array{seconds: int, nanoseconds: int, negative: bool} $addend The second addend
     * @return array{seconds: int, nanoseconds: int, negative: bool} The sum as a new Duration
 * @throws TimeException If the result exceeds the representable range
     */
    public static function add(array $duration, array $addend): array
    {
        if ($duration['negative'] === $addend['negative']) {
            $carry = 0;

            if ($duration['nanoseconds'] >= self::NANOSECONDS_PER_SECOND - $addend['nanoseconds']) {
                $nanoseconds = $duration['nanoseconds'] - (self::NANOSECONDS_PER_SECOND - $addend['nanoseconds']);
                $carry = 1;
            } else {
                $nanoseconds = $duration['nanoseconds'] + $addend['nanoseconds'];
            }

            if ($duration['seconds'] > self::maximumSeconds() - $carry - $addend['seconds']) {
                throw new TimeException(self::OVERFLOW_MESSAGE);
            }

            return self::create(
                $duration['seconds'] + $addend['seconds'] + $carry,
                $nanoseconds,
                $duration['negative']
            );
        }

        $order = self::compareMagnitude($duration, $addend);

        if ($order === 0) {
            return self::create(0, 0, false);
        }

        if ($order > 0) {
            $big = $duration;
            $small = $addend;
        } else {
            $big = $addend;
            $small = $duration;
        }

        $nanoseconds = $big['nanoseconds'] - $small['nanoseconds'];
        $seconds = $big['seconds'] - $small['seconds'];

        if ($nanoseconds < 0) {
            $nanoseconds += self::NANOSECONDS_PER_SECOND;
            $seconds--;
        }

        return self::create($seconds, $nanoseconds, $big['negative']);
    }

    /**
     * Subtracts one Duration from another.
     *
     * Implemented as add(duration, negate(subtrahend)) so that all overflow
     * guarding and sign handling live in a single code path.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $diff = DurationMath::subtract(
     *     DurationMath::fromSeconds(3),
     *     DurationMath::fromSeconds(1)
     * );
     * // 2 seconds
     * ```
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The minuend
 * @param array{seconds: int, nanoseconds: int, negative: bool} $subtrahend The value to subtract
     * @return array{seconds: int, nanoseconds: int, negative: bool} The difference as a new Duration
 * @throws TimeException If the result exceeds the representable range
     */
    public static function subtract(array $duration, array $subtrahend): array
    {
        return self::add($duration, self::negate($subtrahend));
    }

    /**
     * Multiplies a Duration's magnitude by a non-negative integer factor.
     *
     * A factor of zero produces a non-negative zero regardless of the input
     * sign. A factor of one returns an unchanged copy. Three exact guards
     * ensure no false positives and no missed overflow.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $product = DurationMath::multiplyBy(
     *     DurationMath::fromMilliseconds(100),
     *     2 ** 5
     * );
     * // 3.2 seconds
     * ```
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The Duration to scale
 * @param int $factor The non-negative multiplier
     * @return array{seconds: int, nanoseconds: int, negative: bool} The scaled Duration
 * @throws ValueError If factor is negative
 * @throws TimeException If the result exceeds the representable range
     */
    public static function multiplyBy(array $duration, int $factor): array
    {
        if ($factor < 0) {
            throw new ValueError('Time\Duration::multiplyBy(): Argument #1 ($factor) must not be negative');
        }

        if ($factor === 0) {
            return self::create(0, 0, false);
        }

        if ($factor === 1) {
            return $duration;
        }

        $nanoseconds = $duration['nanoseconds'];
        $seconds = $duration['seconds'];

        if ($nanoseconds !== 0 && $factor > intdiv(\PHP_INT_MAX, $nanoseconds)) {
            throw new TimeException(self::OVERFLOW_MESSAGE);
        }

        $nanosecondsProduct = $nanoseconds * $factor;
        $carry = intdiv($nanosecondsProduct, self::NANOSECONDS_PER_SECOND);
        $resultNanoseconds = $nanosecondsProduct % self::NANOSECONDS_PER_SECOND;

        if ($seconds !== 0 && $factor > intdiv(self::maximumSeconds(), $seconds)) {
            throw new TimeException(self::OVERFLOW_MESSAGE);
        }

        $secondsProduct = $seconds * $factor;

        if ($secondsProduct > self::maximumSeconds() - $carry) {
            throw new TimeException(self::OVERFLOW_MESSAGE);
        }

        return self::create($secondsProduct + $carry, $resultNanoseconds, $duration['negative']);
    }

    /**
     * Divides a Duration's magnitude by a positive integer divisor, truncating toward zero.
     *
     * On 64-bit builds the division is performed in a single int64 intermediate.
     * On 32-bit builds the intermediate would overflow, so the global bcmath
     * functions (guaranteed present via phpseclib/bcmath_compat) are used.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $quotient = DurationMath::divideBy(DurationMath::fromSeconds(1), 2);
     * // 0 seconds, 500_000_000 nanoseconds
     * ```
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The Duration to divide
 * @param int $divisor The positive integer to divide by
     * @return array{seconds: int, nanoseconds: int, negative: bool} The truncated quotient
 * @throws ValueError If divisor is not positive
     */
    public static function divideBy(array $duration, int $divisor): array
    {
        if ($divisor <= 0) {
            throw new ValueError('Time\Duration::divideBy(): Argument #1 ($divisor) must be positive');
        }

        $seconds = $duration['seconds'];
        $nanoseconds = $duration['nanoseconds'];

        if (self::supportsWideIntegers()) {
            $total = $seconds * self::NANOSECONDS_PER_SECOND + $nanoseconds;
            $quotient = intdiv($total, $divisor);
        } else {
            $total = bcadd(bcmul((string) $seconds, '1000000000'), (string) $nanoseconds);
            $quotient = (int) bcdiv($total, (string) $divisor, 0);
        }

        $resultSeconds = intdiv($quotient, self::NANOSECONDS_PER_SECOND);
        $resultNanoseconds = $quotient % self::NANOSECONDS_PER_SECOND;

        return self::create($resultSeconds, $resultNanoseconds, $duration['negative']);
    }

    // =========================================================================
    // Comparison
    // =========================================================================

    /**
     * Compares two Durations, returning -1, 0 or 1.
     *
     * Comparison is by sign first (a negative Duration is always less than a
     * non-negative one), then by magnitude. Zero is never negative, so the
     * sign shortcut is safe.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $result = DurationMath::compare(
     *     DurationMath::fromSeconds(1),
     *     DurationMath::fromSeconds(2)
     * );
     * // -1
     * ```
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $a The left operand
 * @param array{seconds: int, nanoseconds: int, negative: bool} $b The right operand
     * @return int -1 if a < b, 0 if equal, 1 if a > b
     */
    public static function compare(array $a, array $b): int
    {
        if ($a['negative'] !== $b['negative']) {
            return $a['negative'] ? -1 : 1;
        }

        $magnitude = self::compareMagnitude($a, $b);

        return $a['negative'] ? -$magnitude : $magnitude;
    }

    /**
     * Computes the signed seconds component used by the comparison-property trick.
     *
     * The hidden comparables declared first in the Duration class body let the
     * native zend_std_compare_objects() walker order two Duration objects
     * correctly under raw <, >, <=, >= and <=>. This method re-derives the
     * value from the public triple on every call so that tampering with the
     * public properties on the PHP 8.0 tier (where readonly is not enforced)
     * cannot corrupt the result of any DurationMath operation.
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The Duration to inspect
     * @return int The signed seconds value (negative magnitudes are <= 0)
     */
    public static function comparableSeconds(array $duration): int
    {
        return $duration['negative'] ? -$duration['seconds'] : $duration['seconds'];
    }

    /**
     * Computes the signed nanoseconds component used by the comparison-property trick.
     *
     * Together with comparableSeconds this pair forms a lexicographic key that
     * is monotone in the true Duration value.
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The Duration to inspect
     * @return int The signed nanoseconds value (negative magnitudes are <= 0)
     */
    public static function comparableNanoseconds(array $duration): int
    {
        return $duration['negative'] ? -$duration['nanoseconds'] : $duration['nanoseconds'];
    }

    // =========================================================================
    // ISO-8601 parsing
    // =========================================================================

    /**
     * Parses an ISO-8601 duration string into a Duration shape.
     *
     * Only the time component is accepted (PT prefix), with hours, minutes and
     * seconds as the largest allowed units. Date components (days, weeks,
     * months, years) are rejected. Fractional values are permitted only on the
     * seconds field. A leading sign character is not accepted; negatives arise
     * through negate() or sub() instead.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Time\DurationMath;
     *
     * $d = DurationMath::fromIso8601DurationString('PT1H30M15S');
     * // 1 hour, 30 minutes, 15 seconds
     * ```
     *
 * @param string $specification The ISO-8601 duration string to parse
     * @return array{seconds: int, nanoseconds: int, negative: bool} The parsed Duration
 * @throws TimeException If the string is not a valid ISO-8601 duration or overflows the range
     */
    public static function fromIso8601DurationString(string $specification): array
    {
        if (preg_match(self::ISO_8601_PATTERN, $specification, $matches) !== 1) {
            throw new TimeException(sprintf('Unable to parse the ISO-8601 duration string "%s"', $specification));
        }

        $hours = $matches[1] ?? null;
        $minutes = $matches[2] ?? null;
        $seconds = $matches[3] ?? null;
        $fraction = $matches[4] ?? null;

        if ($hours === null && $minutes === null && $seconds === null) {
            throw new TimeException(sprintf('Unable to parse the ISO-8601 duration string "%s"', $specification));
        }

        $result = self::create(0, 0, false);

        if ($hours !== null) {
            $hoursValue = self::parseComponentDigits($hours, intdiv(self::maximumSeconds(), self::SECONDS_PER_HOUR));
            $result = self::add($result, self::fromHours($hoursValue));
        }

        if ($minutes !== null) {
            $minutesValue = self::parseComponentDigits(
                $minutes,
                intdiv(self::maximumSeconds(), self::SECONDS_PER_MINUTE)
            );
            $result = self::add($result, self::fromMinutes($minutesValue));
        }

        if ($seconds !== null) {
            $secondsValue = self::parseComponentDigits($seconds, self::maximumSeconds());
            $nanoseconds = 0;

            if ($fraction !== null) {
                $nanoseconds = (int) str_pad($fraction, 9, '0');
            }

            $result = self::add($result, self::fromSeconds($secondsValue, $nanoseconds));
        }

        return $result;
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Reports whether a Duration's magnitude is zero.
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $duration The Duration to test
     * @return bool True when both seconds and nanoseconds are zero
     */
    private static function isZero(array $duration): bool
    {
        return $duration['seconds'] === 0 && $duration['nanoseconds'] === 0;
    }

    /**
     * Compares two Durations by magnitude only, ignoring sign.
     *
 * @param array{seconds: int, nanoseconds: int, negative: bool} $a The left operand
 * @param array{seconds: int, nanoseconds: int, negative: bool} $b The right operand
     * @return int -1, 0 or 1
     */
    private static function compareMagnitude(array $a, array $b): int
    {
        return ($a['seconds'] <=> $b['seconds']) ?: ($a['nanoseconds'] <=> $b['nanoseconds']);
    }

    /**
     * Safely casts a digit run from an ISO-8601 component to an int.
     *
     * PHP silently saturates over-long digit strings to PHP_INT_MAX when
     * casting to int, so the string must be range-checked before the cast.
     * Leading zeros are stripped first; if the remaining length exceeds that
     * of the maximum, or the lengths are equal and the string compares greater,
     * a TimeException is thrown.
     *
 * @param string $digits The digit run captured from the regex
 * @param int $maximum The largest value this component may hold
     * @return int The validated integer value
 * @throws TimeException If the digit run represents a number greater than $maximum
     */
    private static function parseComponentDigits(string $digits, int $maximum): int
    {
        $trimmed = ltrim($digits, '0');

        if ($trimmed === '') {
            return 0;
        }

        $maximumString = (string) $maximum;

        if (
            strlen($trimmed) > strlen($maximumString)
            || (strlen($trimmed) === strlen($maximumString) && strcmp($trimmed, $maximumString) > 0)
        ) {
            throw new TimeException(self::OVERFLOW_MESSAGE);
        }

        return (int) $trimmed;
    }
}
