<?php

declare(strict_types=1);

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
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
        public const AwayFromZero = 'AwayFromZero';

        // phpcs:ignore
        public const HalfAwayFromZero = 'HalfAwayFromZero';

        // phpcs:ignore
        public const HalfEven = 'HalfEven';

        // phpcs:ignore
        public const HalfOdd = 'HalfOdd';

        // phpcs:ignore
        public const HalfTowardsZero = 'HalfTowardsZero';

        // phpcs:ignore
        public const NegativeInfinity = 'NegativeInfinity';

        // phpcs:ignore
        public const PositiveInfinity = 'PositiveInfinity';

        // phpcs:ignore
        public const TowardsZero = 'TowardsZero';
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80400) {
    require_once realpath(__DIR__ . '/../stubs')
        . '/RoundingMode.php';
}
