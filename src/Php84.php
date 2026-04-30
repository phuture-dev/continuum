<?php

declare(strict_types=1);

namespace Phuture\Continuum;

/**
 * PHP 8.4 polyfill methods.
 *
 * This class provides static methods to polyfill PHP 8.4 functions that are not
 * covered by Symfony's polyfill packages. Some methods are fully implemented
 * polyfills, while others are stubs that throw exceptions for functions that
 * cannot be polyfilled in userland PHP.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Php84
{
    /**
     * The system binary directory path.
     *
     * This constant provides a polyfill for the PHP_SBINDIR constant introduced in PHP 8.4.
     * The actual constant contains the path where system executables are installed.
     * Since this information is not available at runtime in older PHP versions,
     * this polyfill returns a common default path.
     *
     * @see https://www.php.net/manual/en/reserved.constants.php
     */
    public const PHP_SBINDIR = '/usr/local/sbin';
}
