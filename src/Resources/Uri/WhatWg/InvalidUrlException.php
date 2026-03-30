<?php

declare(strict_types=1);

namespace Uri\WhatWg;

use Throwable;
use ValueError;
use Uri\InvalidUriException;

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
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
         * @param string $message The exception message
         * @param array $errors List of validation errors from URL parsing
         * @param int $code The exception code
         * @param Throwable|null $previous Previous exception for chaining
         */
        public function __construct(string $message, array $errors, int $code = 0, ?Throwable $previous = null)
        {
            if (!array_is_list($errors)) {
                throw new ValueError('the error argument must be a list.');
            }

            $this->errors = $errors;
            $errorTypes = array_map(fn ($e) => $e->type, $errors);

            if (!empty($errorTypes)) {
                $message .= ' (' . implode(', ', $errorTypes) . ')';
            }

            parent::__construct($message, $code, $previous);
        }
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80500) {
    require_once realpath(__DIR__ . '/../../../../components/league/uri-polyfill/')
        . '/lib/WhatWg/InvalidUrlException.php';
}
