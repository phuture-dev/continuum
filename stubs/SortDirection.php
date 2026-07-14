<?php

declare(strict_types=1);

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80600) {
    /**
     * SortDirection enum polyfill for PHP 8.6.
     * This is a user-land polyfill for the native SortDirection enum
     * included in PHP 8.6. It is loaded on PHP 8.1 to 8.5, where enums
     * are supported by the parser but the native enum does not exist.
     * On PHP 8.0, a stub class with constants is provided instead.
     * The SortDirection enum represents the direction used when sorting
     * a collection of values, providing a type-safe alternative to
     * boolean or integer sort direction flags.
     *
     * @see https://php.watch/versions/8.6/SortDirection
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    // phpcs:ignore
    enum SortDirection
    {
        case Ascending;
        case Descending;
    }
}
