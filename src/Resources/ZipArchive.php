<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

if (!class_exists('ZipArchive')) {
    /**
     * ZipArchive polyfill class.
     *
     * A drop-in replacement for PHP's built-in ZipArchive class (from ext-zip) for environments
     * where the zip extension is not available.
     *
     * The public API — constants, properties, and method signatures, exactly mirrors
     * the native ZipArchive class.
     *
     * @see https://www.php.net/manual/en/class.ziparchive.php
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    // phpcs:ignore
    final class ZipArchive extends Phuture\Continuum\Extension\ZipArchive
    {
    }
}
