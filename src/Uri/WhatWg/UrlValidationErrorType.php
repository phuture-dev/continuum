<?php

declare(strict_types=1);

namespace Phuture\Continuum\Uri\WhatWg;

/**
 * UrlValidationErrorType enum stub for PHP 8.5.
 *
 * This stub enum exists for type compatibility with PHP 8.5+ code.
 * On PHP versions < 8.1, enums are not supported by the parser,
 * so this file will only be loaded when the native UrlValidationErrorType
 * enum does not exist (PHP < 8.5).
 *
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
class UrlValidationErrorType
{
    // phpcs:ignore
    public const DomainInvalidCodePoint = 'DomainInvalidCodePoint';

    // phpcs:ignore
    public const DomainToAscii = 'DomainToAscii';

    // phpcs:ignore
    public const DomainToUnicode = 'DomainToUnicode';

    // phpcs:ignore
    public const FileInvalidWindowsDriveLetter = 'FileInvalidWindowsDriveLetter';

    // phpcs:ignore
    public const FileInvalidWindowsDriveLetterHost = 'FileInvalidWindowsDriveLetterHost';

    // phpcs:ignore
    public const HostInvalidCodePoint = 'HostInvalidCodePoint';

    // phpcs:ignore
    public const HostMissing = 'HostMissing';

    // phpcs:ignore
    public const InvalidCredentials = 'InvalidCredentials';

    // phpcs:ignore
    public const InvalidReverseSolidus = 'InvalidReverseSolidus';

    // phpcs:ignore
    public const InvalidUrlUnit = 'InvalidUrlUnit';

    // phpcs:ignore
    public const Ipv4EmptyPart = 'Ipv4EmptyPart';

    // phpcs:ignore
    public const Ipv4InIpv6InvalidCodePoint = 'Ipv4InIpv6InvalidCodePoint';

    // phpcs:ignore
    public const Ipv4InIpv6OutOfRangePart = 'Ipv4InIpv6OutOfRangePart';

    // phpcs:ignore
    public const Ipv4InIpv6TooFewParts = 'Ipv4InIpv6TooFewParts';

    // phpcs:ignore
    public const Ipv4InIpv6TooManyPieces = 'Ipv4InIpv6TooManyPieces';

    // phpcs:ignore
    public const Ipv4NonDecimalPart = 'Ipv4NonDecimalPart';

    // phpcs:ignore
    public const Ipv4NonNumericPart = 'Ipv4NonNumericPart';

    // phpcs:ignore
    public const Ipv4OutOfRangePart = 'Ipv4OutOfRangePart';

    // phpcs:ignore
    public const Ipv4TooManyParts = 'Ipv4TooManyParts';

    // phpcs:ignore
    public const Ipv6InvalidCodePoint = 'Ipv6InvalidCodePoint';

    // phpcs:ignore
    public const Ipv6InvalidCompression = 'Ipv6InvalidCompression';

    // phpcs:ignore
    public const Ipv6MultipleCompression = 'Ipv6MultipleCompression';

    // phpcs:ignore
    public const Ipv6TooFewPieces = 'Ipv6TooFewPieces';

    // phpcs:ignore
    public const Ipv6TooManyPieces = 'Ipv6TooManyPieces';

    // phpcs:ignore
    public const Ipv6Unclosed = 'Ipv6Unclosed';

    // phpcs:ignore
    public const MissingSchemeNonRelativeUrl = 'MissingSchemeNonRelativeUrl';

    // phpcs:ignore
    public const PortInvalid = 'PortInvalid';

    // phpcs:ignore
    public const PortOutOfRange = 'PortOutOfRange';

    // phpcs:ignore
    public const SpecialSchemeMissingFollowingSolidus = 'SpecialSchemeMissingFollowingSolidus';
}
