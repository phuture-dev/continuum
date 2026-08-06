<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace Time;

use Exception;

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000) {
    /**
     * Base exception class for Time-related errors.
     *
     * This stub provides the Time\TimeException class introduced in PHP 8.6
     * for builds where the native class is not yet available. It is reached
     * only through the classmap autoloader, so on builds that ship the native
     * class this file is never included and the native class always wins.
     *
     * @see https://wiki.php.net/rfc/duration_class
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    class TimeException extends Exception
    {
    }
}
