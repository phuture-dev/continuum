<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
    /**
     * SortDirection enum stub for PHP 8.6.
     *
     * This stub class exists for type compatibility with PHP 8.6+ code.
     * On PHP versions < 8.1, enums are not supported by the parser,
     * so this class provides the enum cases as constants instead. This
     * file will only be loaded when the native SortDirection enum does
     * not exist (PHP < 8.6).
     *
     * The SortDirection enum represents the direction used when sorting
     * a collection of values, providing a type-safe alternative to
     * boolean or integer sort direction flags.
     *
     * @see https://php.watch/versions/8.6/SortDirection
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    // phpcs:ignore
    final class SortDirection
    {
        // phpcs:ignore
        public const Ascending = 'ASC';

        // phpcs:ignore
        public const Descending = 'DESC';
    }
}
