<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace Time;

use ValueError;
use Phuture\Continuum\Time\DurationMath;

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
    /**
     * Represents a stopwatch-style duration with nanosecond precision.
     *
     * This is the PHP 8.0 tier of the polyfill. PHP 8.0 has no readonly
     * keyword, so the public properties are mutable; @readonly in the
     * PHPDoc signals intent to humans and IDEs. All other behaviour is
     * identical to the 8.1+ tier defined in stubs/Time/Duration.php.
     *
     * Two private implementation properties are declared first in the class
     * body so that PHP's default object-comparison walker visits them before
     * the public magnitude triple. This lets the raw <, >, <=, >= and <=>
     * operators order Duration values correctly, including across the
     * negative/positive boundary, without needing the native comparison
     * handler that only the internal class possesses. Because readonly is not
     * enforced on this tier, DurationMath re-derives the comparable values
     * from the public triple on every operation to guard against tampering.
     *
     * @see https://wiki.php.net/rfc/duration_class
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    final class Duration
    {
        /**
         * Signed seconds used only by the default object-comparison walker.
         *
         * Declared first so it sorts before the nanosecond component.
         *
         * @var int
         */
        private int $comparableSeconds;

        /**
         * Signed nanoseconds used only by the default object-comparison walker.
         *
         * Together with comparableSeconds this pair forms a lexicographic key
         * that is monotone in the true Duration value.
         *
         * @var int
         */
        private int $comparableNanoseconds;

        /**
         * The whole-second part of the magnitude, always non-negative.
         *
         * @readonly
         * @var int
         */
        public int $seconds;

        /**
         * The sub-second part of the magnitude, in the range 0 to 999_999_999.
         *
         * @readonly
         * @var int
         */
        public int $nanoseconds;

        /**
         * Whether the duration is negative. Zero is never negative.
         *
         * @readonly
         * @var bool
         */
        public bool $negative;

        /**
         * Creates a Duration from validated components.
         *
         * The constructor is private; use one of the from* factory methods
         * or fromIso8601DurationString() to obtain an instance.
         *
 * @param int $seconds The whole-second part (must be >= 0)
 * @param int $nanoseconds The sub-second part (0 to 999_999_999)
 * @param bool $negative Whether the duration is negative
         */
        private function __construct(int $seconds, int $nanoseconds, bool $negative)
        {
            $this->seconds = $seconds;
            $this->nanoseconds = $nanoseconds;
            $this->negative = $negative;
            $this->comparableSeconds = $negative ? -$seconds : $seconds;
            $this->comparableNanoseconds = $negative ? -$nanoseconds : $nanoseconds;
        }

        /**
         * Creates a Duration from whole seconds and an optional nanosecond remainder.
         *
         * Example:
         * ```php
         * $d = \Time\Duration::fromSeconds(3, 500_000_000);
         * // 3.5 seconds
         * ```
         *
 * @param int $seconds The whole-second part (must be >= 0)
 * @param int $nanoseconds The sub-second remainder (0 to 999_999_999, default: 0)
 * @throws ValueError If seconds or nanoseconds is negative, or nanoseconds is out of range
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public static function fromSeconds(int $seconds, int $nanoseconds = 0): self
        {
            return new self(...DurationMath::fromSeconds($seconds, $nanoseconds));
        }

        /**
         * Creates a Duration from a total number of nanoseconds.
         *
 * @param int $nanoseconds The total number of nanoseconds (must be >= 0)
 * @throws ValueError If nanoseconds is negative
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public static function fromNanoseconds(int $nanoseconds): self
        {
            return new self(...DurationMath::fromNanoseconds($nanoseconds));
        }

        /**
         * Creates a Duration from a total number of microseconds.
         *
 * @param int $microseconds The total number of microseconds (must be >= 0)
 * @throws ValueError If microseconds is negative
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public static function fromMicroseconds(int $microseconds): self
        {
            return new self(...DurationMath::fromMicroseconds($microseconds));
        }

        /**
         * Creates a Duration from a total number of milliseconds.
         *
 * @param int $milliseconds The total number of milliseconds (must be >= 0)
 * @throws ValueError If milliseconds is negative
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public static function fromMilliseconds(int $milliseconds): self
        {
            return new self(...DurationMath::fromMilliseconds($milliseconds));
        }

        /**
         * Creates a Duration from a number of whole minutes.
         *
 * @param int $minutes The number of minutes (must be >= 0)
 * @throws ValueError If minutes is negative
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public static function fromMinutes(int $minutes): self
        {
            return new self(...DurationMath::fromMinutes($minutes));
        }

        /**
         * Creates a Duration from a number of whole hours.
         *
 * @param int $hours The number of hours (must be >= 0)
 * @throws ValueError If hours is negative
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public static function fromHours(int $hours): self
        {
            return new self(...DurationMath::fromHours($hours));
        }

        /**
         * Parses an ISO-8601 duration string into a Duration.
         *
         * Only the time component (PT prefix) is accepted; the largest unit
         * is hours. Date components are rejected. Fractions are permitted only
         * on the seconds field.
         *
 * @param string $specification The ISO-8601 duration string to parse
 * @throws \Time\TimeException If the string is invalid or the result overflows
         */
        public static function fromIso8601DurationString(string $specification): self
        {
            return new self(...DurationMath::fromIso8601DurationString($specification));
        }

        /**
         * Returns a new Duration with the sign flipped.
         *
         * Negating a zero Duration yields a non-negative zero.
         */
        public function negate(): self
        {
            return new self(...DurationMath::negate([
                'seconds' => $this->seconds,
                'nanoseconds' => $this->nanoseconds,
                'negative' => $this->negative,
            ]));
        }

        /**
         * Returns a new Duration with the negative flag cleared.
         */
        public function absolute(): self
        {
            return new self(...DurationMath::absolute([
                'seconds' => $this->seconds,
                'nanoseconds' => $this->nanoseconds,
                'negative' => $this->negative,
            ]));
        }

        /**
         * Returns a new Duration representing the sum of this and another.
         *
 * @param self $duration The addend
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public function add(self $duration): self
        {
            return new self(...DurationMath::add(
                [
                    'seconds' => $this->seconds,
                    'nanoseconds' => $this->nanoseconds,
                    'negative' => $this->negative,
                ],
                [
                    'seconds' => $duration->seconds,
                    'nanoseconds' => $duration->nanoseconds,
                    'negative' => $duration->negative,
                ]
            ));
        }

        /**
         * Returns a new Duration representing the difference between this and another.
         *
 * @param self $duration The subtrahend
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public function sub(self $duration): self
        {
            return new self(...DurationMath::subtract(
                [
                    'seconds' => $this->seconds,
                    'nanoseconds' => $this->nanoseconds,
                    'negative' => $this->negative,
                ],
                [
                    'seconds' => $duration->seconds,
                    'nanoseconds' => $duration->nanoseconds,
                    'negative' => $duration->negative,
                ]
            ));
        }

        /**
         * Returns a new Duration with the magnitude scaled by a non-negative factor.
         *
 * @param int $factor The non-negative multiplier
 * @throws ValueError If factor is negative
 * @throws \Time\TimeException If the result exceeds the representable range
         */
        public function multiplyBy(int $factor): self
        {
            return new self(...DurationMath::multiplyBy(
                [
                    'seconds' => $this->seconds,
                    'nanoseconds' => $this->nanoseconds,
                    'negative' => $this->negative,
                ],
                $factor
            ));
        }

        /**
         * Returns a new Duration with the magnitude divided by a positive divisor.
         *
         * Truncation is toward zero.
         *
 * @param int $divisor The positive divisor
 * @throws ValueError If divisor is not positive
         */
        public function divideBy(int $divisor): self
        {
            return new self(...DurationMath::divideBy(
                [
                    'seconds' => $this->seconds,
                    'nanoseconds' => $this->nanoseconds,
                    'negative' => $this->negative,
                ],
                $divisor
            ));
        }

        /**
         * Compares two Durations, returning -1, 0 or 1.
         *
 * @param self $a The left operand
 * @param self $b The right operand
         * @return int -1 if a < b, 0 if equal, 1 if a > b
         */
        public static function compare(self $a, self $b): int
        {
            return DurationMath::compare(
                [
                    'seconds' => $a->seconds,
                    'nanoseconds' => $a->nanoseconds,
                    'negative' => $a->negative,
                ],
                [
                    'seconds' => $b->seconds,
                    'nanoseconds' => $b->nanoseconds,
                    'negative' => $b->negative,
                ]
            );
        }

        /**
         * Returns the three public properties for debug output.
         *
         * @return array<string, int|bool>
         */
        public function __debugInfo(): array
        {
            return [
                'seconds' => $this->seconds,
                'nanoseconds' => $this->nanoseconds,
                'negative' => $this->negative,
            ];
        }
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100) {
    require_once __DIR__ . '/../../../stubs/Time/Duration.php';
}
