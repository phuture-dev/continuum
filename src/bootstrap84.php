<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Phuture\Continuum\Php84;

if (\PHP_VERSION_ID >= 80400) {
    return;
}

/**
 * PHP 8.4 constants
 */
if (!defined('PHP_SBINDIR')) {
    define('PHP_SBINDIR', Php84::PHP_SBINDIR);
}
