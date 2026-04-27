<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace Uri;

use Throwable;

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
    /**
     * Exception thrown when a URI is invalid.
     *
     * @see https://tools.ietf.org/html/rfc3986
     * @see https://wiki.php.net/rfc/url_parsing_api
     * @see https://www.php.net/releases/8.5/en.php#new-uri-extension
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    class InvalidUriException extends UriException
    {
        /**
         * Constructs a new InvalidUriException.
         *
         * @param string $message The exception message
         * @param int $code The exception code
         * @param Throwable|null $previous Previous exception for chaining
         */
        public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
        {
            parent::__construct('The specified URI is malformed; ' . $message, $code, $previous);
        }
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80500) {
    require_once realpath(__DIR__ . '/../../../components/league/uri-polyfill/')
        . '/lib/InvalidUriException.php';
}
