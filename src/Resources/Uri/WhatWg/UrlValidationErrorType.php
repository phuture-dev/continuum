<?php

declare(strict_types=1);

namespace Uri\WhatWg;

if (\PHP_VERSION_ID >= 80100) {
    return require_once __DIR__ . '/../../../../vendor/league/uri-polyfill/lib/WhatWg/UrlValidationErrorType.php';
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
    final class UrlValidationErrorType
    {
        public const DomainInvalidCodePoint = 'DomainInvalidCodePoint';
        public const DomainToAscii = 'DomainToAscii';
        public const DomainToUnicode = 'DomainToUnicode';
        public const FileInvalidWindowsDriveLetter = 'FileInvalidWindowsDriveLetter';
        public const FileInvalidWindowsDriveLetterHost = 'FileInvalidWindowsDriveLetterHost';
        public const HostInvalidCodePoint = 'HostInvalidCodePoint';
        public const HostMissing = 'HostMissing';
        public const InvalidCredentials = 'InvalidCredentials';
        public const InvalidReverseSolidus = 'InvalidReverseSolidus';

        public const InvalidUrlUnit = 'InvalidUrlUnit';

        public const Ipv4EmptyPart = 'Ipv4EmptyPart';
        public const Ipv4InIpv6InvalidCodePoint = 'Ipv4InIpv6InvalidCodePoint';
        public const Ipv4InIpv6OutOfRangePart = 'Ipv4InIpv6OutOfRangePart';
        public const Ipv4InIpv6TooFewParts = 'Ipv4InIpv6TooFewParts';

        public const Ipv4InIpv6TooManyPieces = 'Ipv4InIpv6TooManyPieces';
        public const Ipv4NonDecimalPart = 'Ipv4NonDecimalPart';
        public const Ipv4NonNumericPart = 'Ipv4NonNumericPart';
        public const Ipv4OutOfRangePart = 'Ipv4OutOfRangePart';
        public const Ipv4TooManyParts = 'Ipv4TooManyParts';
        public const Ipv6InvalidCodePoint = 'Ipv6InvalidCodePoint';
        public const Ipv6InvalidCompression = 'Ipv6InvalidCompression';
        public const Ipv6MultipleCompression = 'Ipv6MultipleCompression';
        public const Ipv6TooFewPieces = 'Ipv6TooFewPieces';
        public const Ipv6TooManyPieces = 'Ipv6TooManyPieces';

        public const Ipv6Unclosed = 'Ipv6Unclosed';
        public const MissingSchemeNonRelativeUrl = 'MissingSchemeNonRelativeUrl';
        public const PortInvalid = 'PortInvalid';
        public const PortOutOfRange = 'PortOutOfRange';
        public const SpecialSchemeMissingFollowingSolidus = 'SpecialSchemeMissingFollowingSolidus';

        private function __construct()
        {
        }
    }
}
