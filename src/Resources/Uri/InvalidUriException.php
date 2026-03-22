<?php

declare(strict_types=1);

namespace Uri;

// phpcs:ignore
if (\PHP_VERSION_ID < 80100) {
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
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100) {
    return require_once realpath(\Composer\InstalledVersions::getInstallPath('league/uri-polyfill'))
        . '/lib/InvalidUriException.php';
}
