<?php

declare(strict_types=1);

namespace Uri\WhatWg;

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
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
        /**
         * The context in which the validation error occurred.
         *
         * @var string
         */
        public string $context;

        /**
         * Indicates whether the validation error is a failure.
         *
         * @var bool
         */
        public bool $failure;

        /**
         * The type of validation error.
         *
         * @var string
         */
        public string $type;

        /**
         * Constructs a new URL validation error instance.
         *
         * @param string $context The context in which the validation error occurred
         * @param string $type The type of validation error
         * @param bool $failure Whether the validation error is a failure
         */
        public function __construct(string $context, string $type, bool $failure)
        {
            $this->context = $context;
            $this->type = $type;
            $this->failure = $failure;
        }
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80500) {
    require_once realpath(__DIR__ . '/../../../../components/league/uri-polyfill/')
        . '/lib/WhatWg/UrlValidationError.php';
}
