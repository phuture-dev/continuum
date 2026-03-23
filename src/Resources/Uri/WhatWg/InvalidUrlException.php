<?php

declare(strict_types=1);

namespace Uri\WhatWg;

use Throwable;
use Uri\InvalidUriException;

// phpcs:ignore
if (\PHP_VERSION_ID < 80100) {
    /**
     * Exception thrown when a URL is invalid, containing validation errors.
     *
     * @see https://tools.ietf.org/html/rfc3986
     * @see https://wiki.php.net/rfc/url_parsing_api
     * @see https://www.php.net/releases/8.5/en.php#new-uri-extension
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    class InvalidUrlException extends InvalidUriException
    {
        /**
         * List of validation errors that occurred during URL parsing
         *
         * @var array
         */
        public array $errors;

        /**
         * Constructs a new InvalidUrlException.
         *
         * @param array $errors List of validation errors
         * @param string $message The exception message
         * @param int $code The exception code
         * @param Throwable|null $previous The previous exception for chaining
         */
        public function __construct(array $errors, string $message = '', int $code = 0, ?Throwable $previous = null)
        {
            $this->errors = $errors;
            parent::__construct($message, $code, $previous);
        }
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100) {
    return require_once realpath(\Composer\InstalledVersions::getInstallPath('thephpleague/uri-polyfill'))
        . '/lib/WhatWg/InvalidUrlException.php';
}
