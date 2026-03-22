<?php

declare(strict_types=1);

namespace Uri\WhatWg;

use Throwable;
use Uri\InvalidUriException;

if (\PHP_VERSION_ID >= 80100) {
    return require_once __DIR__ . '/../../../../vendor/league/uri-polyfill/lib/WhatWg/UrlValidationError.php';
}

if (\PHP_VERSION_ID < 80100) {
    /**
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
    final class UrlValidationError
    {
        public readonly string $context;

        public readonly UrlValidationErrorType $type;
        public readonly bool $failure;

        public function __construct(string $context, UrlValidationErrorType $type, bool $failure)
        {
            $this->context = $context;
            $this->type = $type;
            $this->failure = $failure;
        }
    }
}
