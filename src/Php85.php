<?php

declare(strict_types=1);

namespace Phuture\Continuum;

/**
 * PHP 8.5 polyfill methods.
 *
 * This class provides static methods to polyfill PHP 8.5 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php85
{
    /**
     * The PHP build date.
     *
     * This constant provides a polyfill for the PHP_BUILD_DATE constant introduced in PHP 8.5.
     * The actual constant contains the exact date and time when PHP was compiled.
     * Since this information is not available at runtime in older PHP versions,
     * this polyfill returns the release date of this package.
     *
     * @see https://www.php.net/manual/en/reserved.constants.php
     */
    public const PHP_BUILD_DATE = 'Dec 28 2025 00:00:00';

    /**
     * The build provider for this polyfill package.
     *
     * This constant identifies the source of this polyfill implementation.
     * It can be used to distinguish between native PHP 8.5+ and this polyfill.
     */
    public const PHP_BUILD_PROVIDER = 'phuture/continuum';
}
