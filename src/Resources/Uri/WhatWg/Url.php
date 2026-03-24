<?php

declare(strict_types=1);

namespace Uri\WhatWg;

// phpcs:ignore
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80100) {
    /**
     * WHATWG URL Standard compliant URL implementation for PHP 8.0 polyfill.
     *
     * This class provides a complete URL parsing and manipulation API following
     * the WHATWG URL Standard. It supports all URL components including scheme,
     * authority (userinfo, host, port), path, query, and fragment.
     *
     * Key features:
     * - Parse and validate URLs according to WHATWG URL Standard
     * - Access individual URL components via getters
     * - Immutable modifications via with*() methods
     * - Resolve relative references against a base URL
     * - Compare URLs with configurable fragment handling
     * - IDNA support for internationalized domain names
     *
     * On PHP 8.1+, this delegates to the league/uri-polyfill library which
     * provides the native Uri\WhatWg\Url implementation. This polyfill enables
     * code using the PHP 8.5 URI extension API to run on PHP 8.0.
     *
     * @see https://url.spec.whatwg.org/
     * @see https://wiki.php.net/rfc/url_parsing_api
     * @see https://www.php.net/releases/8.5/en.php#new-uri-extension
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    final class Url extends \Phuture\Continuum\Uri\WhatWg\Url
    {
    }
}

// phpcs:ignore
if (\PHP_VERSION_ID >= 80100 && \PHP_VERSION_ID < 80500) {
    require_once realpath(__DIR__ . '/../../../../components/league/uri-polyfill/')
        . '/lib/WhatWg/Url.php';
}
