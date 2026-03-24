<?php

declare(strict_types=1);

namespace Uri\WhatWg;

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
    /**
     * UrlValidationErrorType enum stub for PHP 8.5.
     *
     * This stub enum exists for type compatibility with PHP 8.5+ code.
     * On PHP versions < 8.1, enums are not supported by the parser,
     * so this file will only be loaded when the native UrlValidationErrorType
     * enum does not exist (PHP < 8.5).
     *
     * URL validation error types as defined by WHATWG URL Standard.
     *
     * @see https://tools.ietf.org/html/rfc3986
     * @see https://wiki.php.net/rfc/url_parsing_api
     * @see https://www.php.net/releases/8.5/en.php#new-uri-extension
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    final class UrlValidationErrorType extends \Phuture\Continuum\Uri\WhatWg\UrlValidationErrorType
    {
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80500) {
    require_once realpath(__DIR__ . '/../../../../components/league/uri-polyfill/')
        . '/lib/WhatWg/UrlValidationErrorType.php';
}
