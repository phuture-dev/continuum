<?php

declare(strict_types=1);

namespace Uri\WhatWg;

if (\PHP_VERSION_ID >= 80100) {
    return require_once __DIR__ . '/../../../../vendor/league/uri-polyfill/lib/WhatWg/UrlValidationError.php';
}

if (\PHP_VERSION_ID < 80100) {
    /**
     * Represents a URL validation error with context and failure status.
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
        public string $context;
        public bool $failure;
        public string $type;

        public function __construct(string $context, string $type, bool $failure)
        {
            $this->context = $context;
            $this->type = $type;
            $this->failure = $failure;
        }
    }
}
