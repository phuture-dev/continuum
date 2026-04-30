<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

// phpcs:ignore
if (!class_exists('RoundingMode', false) && \PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
    /**
     * RoundingMode enum stub for PHP 8.4.
     *
     * This stub enum exists for type compatibility with PHP 8.4+ code.
     * On PHP versions < 8.1, enums are not supported by the parser,
     * so this file will only be loaded when the native RoundingMode
     * enum does not exist (PHP < 8.4).
     *
     * The RoundingMode enum defines the rounding modes available for
     * mathematical operations like round() and bcround().
     *
     * @see https://www.php.net/manual/en/enum.roundingmode.php
     * @see https://wiki.php.net/rfc/rounding
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    // phpcs:ignore
    final class RoundingMode
    {
        // phpcs:ignore
        public const HalfAwayFromZero = PHP_ROUND_HALF_UP;

        // phpcs:ignore
        public const HalfTowardsZero = PHP_ROUND_HALF_DOWN;

        // phpcs:ignore
        public const HalfEven = PHP_ROUND_HALF_EVEN;

        // phpcs:ignore
        public const HalfOdd = PHP_ROUND_HALF_ODD;

        // phpcs:ignore
        public const PositiveInfinity = 5;

        // phpcs:ignore
        public const NegativeInfinity = 6;

        // phpcs:ignore
        public const TowardsZero = 7;

        // phpcs:ignore
        public const AwayFromZero = 8;
    }
}
