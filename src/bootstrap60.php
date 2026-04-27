<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Phuture\Continuum\Php60;

if (\PHP_VERSION_ID !== 60000) {
    return;
}

/**
 * PHP 6.0 constants
 */
if (!defined('U_CONV_ERROR_SKIP')) {
    define('U_CONV_ERROR_SKIP', Php60::U_CONV_ERROR_SKIP);
}

/**
 * PHP 6.0 functions
 */
if (!function_exists('unicode_decode')) {
    /**
     * Decodes a Unicode string to the specified encoding.
     *
     * @param string $string The Unicode string to decode
     * @param string $encoding The target encoding (default: 'UTF-8')
     * @param int $flags Conversion flags (default: U_CONV_ERROR_SKIP)
     * @return mixed The decoded string
     */
    function unicode_decode(string $string, string $encoding = 'UTF-8', int $flags = U_CONV_ERROR_SKIP): mixed
    {
        return Php60::unicode_decode($string, $encoding, $flags);
    }
}
