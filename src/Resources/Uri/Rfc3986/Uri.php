<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace Uri\Rfc3986;

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
    /**
     * RFC 3986 compliant URI implementation for PHP 8.0 polyfill.
     *
     * This class provides a complete URI parsing and manipulation API following
     * the RFC 3986 standard. It supports all URI components including scheme,
     * authority (userinfo, host, port), path, query, and fragment.
     *
     * Key features:
     * - Parse and validate URIs according to RFC 3986
     * - Access individual URI components via getters
     * - Immutable modifications via with*() methods
     * - Resolve relative references against a base URI
     * - Compare URIs with configurable fragment handling
     * - Percent-encoding/decoding of components
     *
     * On PHP 8.1+, this delegates to the league/uri-polyfill library which
     * provides the native Uri\Rfc3986\Uri implementation. This polyfill enables
     * code using the PHP 8.5 URI extension API to run on PHP 8.0.
     *
     * @see https://tools.ietf.org/html/rfc3986
     * @see https://wiki.php.net/rfc/url_parsing_api
     * @see https://www.php.net/releases/8.5/en.php#new-uri-extension
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    final class Uri extends \Phuture\Continuum\Uri\Rfc3986\Uri
    {
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80500) {
    require_once realpath(__DIR__ . '/../../../../components/league/uri-polyfill/')
        . '/lib/Rfc3986/Uri.php';
}
