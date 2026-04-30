<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Phuture\Continuum\Php85;

if (\PHP_VERSION_ID >= 80500) {
    return;
}

/**
 * PHP 8.5 constants
 */
if (!defined('PHP_BUILD_DATE')) {
    define('PHP_BUILD_DATE', Php85::PHP_BUILD_DATE);
}

if (!defined('PHP_BUILD_PROVIDER')) {
    define('PHP_BUILD_PROVIDER', Php85::PHP_BUILD_PROVIDER);
}
