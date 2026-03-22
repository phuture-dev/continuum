<?php

declare(strict_types=1);

// phpcs:ignore
if (\PHP_VERSION_ID < 80100) {
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
        public const AwayFromZero = 8;

        // phpcs:ignore
        public const HalfAwayFromZero = 1;

        // phpcs:ignore
        public const HalfEven = 3;

        // phpcs:ignore
        public const HalfOdd = 4;

        // phpcs:ignore
        public const HalfTowardsZero = 2;

        // phpcs:ignore
        public const NegativeInfinity = 6;

        // phpcs:ignore
        public const PositiveInfinity = 5;

        // phpcs:ignore
        public const TowardsZero = 7;
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80400 && !enum_exists(RoundingMode::class)) {
    $enum = <<<'RoundingMode'
    enum RoundingMode: int
    {
        case HalfAwayFromZero = 1;
        case HalfTowardsZero = 2;
        case HalfEven = 3;
        case HalfOdd = 4;
        case PositiveInfinity = 5;
        case NegativeInfinity = 6;
        case TowardsZero = 7;
        case AwayFromZero = 8;
    }
    RoundingMode;
    eval($enum);
}
