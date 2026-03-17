<?php

declare(strict_types=1);

namespace Uri\WhatWg;

use Uri\{UriComparisonMode, InvalidUrlException};

if (\PHP_VERSION_ID >= 80100) {
    return require_once __DIR__ . '/../../../../vendor/league/uri-polyfill/lib/WhatWg/Url.php';
}

if (\PHP_VERSION_ID < 80100) {
    final class Url
    {
        private const SPECIAL_SCHEMES = [
            'ftp' => 21,
            'file' => null,
            'http' => 80,
            'https' => 443,
            'ws' => 80,
            'wss' => 443,
        ];
        private ?string $asciiHost = null;
        private ?string $fragment = null;
        private ?string $password = null;
        private string $path = '';
        private ?int $port = null;
        private ?string $query = null;
        private string $scheme = '';
        private ?string $unicodeHost = null;
        private ?string $username = null;

        /**
         * Constructs a Url instance from a URL string.
         *
         * @param string $url The URL string to parse
         * @param self|null $baseUrl Optional base URL for resolving relative URLs
         * @param array|null $softErrors Optional reference to collect non-fatal validation errors
         * @throws InvalidUrlException If the URL cannot be parsed
         */
        public function __construct(string $url, ?self $baseUrl = null, &$softErrors = null)
        {
            $errors = [];
            $this->parseUrl($url, $baseUrl, $errors);

            // Filter for failures only
            $failures = array_filter($errors, fn ($e) => $e->failure);

            if (!empty($failures)) {
                throw new InvalidUrlException(
                    $errors,
                    'Invalid URL: ' . $url
                );
            }

            if ($softErrors !== null) {
                $softErrors = $errors;
            }
        }

        /**
         * String representation of the URL.
         */
        public function __toString(): string
        {
            return $this->toAsciiString();
        }

        /**
         * Checks if this URL equals another URL.
         */
        public function equals(self $other, string $mode = UriComparisonMode::ExcludeFragment): bool
        {
            if ($this->scheme !== $other->scheme) {
                return false;
            }
            if ($this->username !== $other->username) {
                return false;
            }
            if ($this->password !== $other->password) {
                return false;
            }
            if ($this->asciiHost !== $other->asciiHost) {
                return false;
            }
            if ($this->getPort() !== $other->getPort()) {
                return false;
            }
            if ($this->path !== $other->path) {
                return false;
            }
            if ($this->query !== $other->query) {
                return false;
            }

            if ($mode === UriComparisonMode::IncludeFragment) {
                if ($this->fragment !== $other->fragment) {
                    return false;
                }
            }

            return true;
        }

        public function getAsciiHost(): ?string
        {
            return $this->asciiHost;
        }

        public function getFragment(): ?string
        {
            return $this->fragment;
        }

        public function getPassword(): ?string
        {
            return $this->password;
        }

        public function getPath(): string
        {
            return $this->path;
        }

        public function getPort(): ?int
        {
            if ($this->port === null) {
                return null;
            }

            // Return null for default ports of special schemes
            if (isset(self::SPECIAL_SCHEMES[$this->scheme]) &&
                self::SPECIAL_SCHEMES[$this->scheme] === $this->port) {
                return null;
            }

            return $this->port;
        }

        public function getQuery(): ?string
        {
            return $this->query;
        }

        public function getScheme(): string
        {
            return $this->scheme;
        }

        public function getUnicodeHost(): ?string
        {
            return $this->unicodeHost;
        }

        public function getUsername(): ?string
        {
            return $this->username;
        }

        /**
         * Attempts to parse a URL string and returns a Url instance or null on failure.
         *
         * @param string $url The URL string to parse
         * @param self|null $baseUrl Optional base URL for resolving relative URLs
         * @param array|null $errors Optional reference to collect validation errors
         * @return self|null Returns null if the URL cannot be parsed
         */
        public static function parse(string $url, ?self $baseUrl = null, &$errors = null): ?self
        {
            try {
                $softErrors = [];
                $instance = new self($url, $baseUrl, $softErrors);
                if ($errors !== null) {
                    $errors = $softErrors;
                }

                return $instance;
            } catch (InvalidUrlException $e) {
                if ($errors !== null) {
                    $errors = $e->errors;
                }

                return null;
            }
        }

        /**
         * Resolves a relative reference against this URL.
         */
        public function resolve(string $reference): self
        {
            return new self($reference, $this);
        }

        /**
         * Returns the URL with ASCII (Punycode) encoded host.
         */
        public function toAsciiString(): string
        {
            return $this->buildUrl($this->asciiHost);
        }

        /**
         * Returns the URL with Unicode (IDN) decoded host.
         */
        public function toUnicodeString(): string
        {
            return $this->buildUrl($this->unicodeHost);
        }

        public function withFragment(?string $fragment): self
        {
            $new = clone $this;
            $new->fragment = $fragment;

            return $new;
        }

        public function withHost(?string $host): self
        {
            $new = clone $this;
            $errors = [];
            $new->parseHost($host ?? '', $errors);

            return $new;
        }

        public function withPassword(?string $password): self
        {
            $new = clone $this;
            $new->password = $password;

            return $new;
        }

        public function withPath(string $path): self
        {
            $new = clone $this;
            $new->path = $new->normalizePath($path);

            return $new;
        }

        public function withPort(?int $port): self
        {
            $new = clone $this;
            $new->port = $port;

            return $new;
        }

        public function withQuery(?string $query): self
        {
            $new = clone $this;
            $new->query = $query;

            return $new;
        }

        public function withScheme(string $scheme): self
        {
            $new = clone $this;
            $new->scheme = strtolower($scheme);

            return $new;
        }

        public function withUsername(?string $username): self
        {
            $new = clone $this;
            $new->username = $username;

            return $new;
        }

        /**
         * Builds the URL string from components.
         */
        private function buildUrl(?string $host): string
        {
            $result = $this->scheme . ':';

            if ($host !== null) {
                $result .= '//';

                if ($this->username !== null) {
                    $result .= $this->encodePercent($this->username);
                    if ($this->password !== null) {
                        $result .= ':' . $this->encodePercent($this->password);
                    }
                    $result .= '@';
                }

                // IPv6 brackets
                if ($this->isValidIpv6(trim($host, '[]'))) {
                    $result .= '[' . trim($host, '[]') . ']';
                } else {
                    $result .= $host;
                }

                $port = $this->getPort();
                if ($port !== null) {
                    $result .= ':' . $port;
                }
            }

            $result .= $this->path;

            if ($this->query !== null) {
                $result .= '?' . $this->query;
            }

            if ($this->fragment !== null) {
                $result .= '#' . $this->fragment;
            }

            return $result;
        }

        private function decodePercent(string $string): string
        {
            return preg_replace_callback('/%([0-9A-Fa-f]{2})/', function ($match) {
                return chr(hexdec($match[1]));
            }, $string);
        }

        private function encodePercent(string $string, string $extraAllowed = ''): string
        {
            $result = '';
            $length = strlen($string);
            $allowed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~' . $extraAllowed;

            for ($i = 0; $i < $length; $i++) {
                $char = $string[$i];
                if (str_contains($allowed, $char)) {
                    $result .= $char;
                } else {
                    $result .= sprintf('%%%02X', ord($char));
                }
            }

            return $result;
        }

        /**
         * Finds the end of the authority component.
         */
        private function findAuthorityEnd(string $string): int
        {
            $len = strlen($string);
            for ($i = 0; $i < $len; $i++) {
                $char = $string[$i];
                if ($char === '/' || $char === '?' || $char === '#') {
                    return $i;
                }
            }

            return $len;
        }

        /**
         * Finds the end of the path component.
         */
        private function findPathEnd(string $string): int
        {
            $queryPos = strpos($string, '?');
            $fragmentPos = strpos($string, '#');

            if ($queryPos === false && $fragmentPos === false) {
                return strlen($string);
            }

            if ($queryPos === false) {
                return $fragmentPos;
            }
            if ($fragmentPos === false) {
                return $queryPos;
            }

            return min($queryPos, $fragmentPos);
        }

        private function isSpecialScheme(): bool
        {
            return isset(self::SPECIAL_SCHEMES[$this->scheme]);
        }

        private function isValidHostCodePoints(string $host): bool
        {
            // Check for forbidden code points
            $forbidden = "\x00\t\n\r #/:?@[\\]";
            for ($i = 0; $i < strlen($forbidden); $i++) {
                if (str_contains($host, $forbidden[$i])) {
                    return false;
                }
            }

            return true;
        }

        private function isValidIpv4(string $ip): bool
        {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        }

        private function isValidIpv6(string $ip): bool
        {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }

        private function isValidScheme(string $scheme): bool
        {
            return preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*$/', $scheme) === 1;
        }

        /**
         * Normalizes a path by removing . and .. segments.
         */
        private function normalizePath(string $path): string
        {
            $segments = explode('/', $path);
            $result = [];
            $isAbsolute = str_starts_with($path, '/');
            $hasTrailingSlash = str_ends_with($path, '/') && strlen($path) > 1;

            foreach ($segments as $segment) {
                if ($segment === '' || $segment === '.') {
                    continue;
                }
                if ($segment === '..') {
                    if (!empty($result) && end($result) !== '..') {
                        array_pop($result);
                    } elseif (!$isAbsolute) {
                        $result[] = '..';
                    }
                    continue;
                }
                $result[] = $segment;
            }

            $normalizedPath = implode('/', $result);

            if ($isAbsolute) {
                $normalizedPath = '/' . $normalizedPath;
            }

            if ($hasTrailingSlash && !str_ends_with($normalizedPath, '/')) {
                $normalizedPath .= '/';
            }

            return $normalizedPath ?: ($isAbsolute ? '/' : '');
        }

        /**
         * Parses the authority component (userinfo@host:port).
         */
        private function parseAuthority(string $authority, array &$errors): void
        {
            // Find @ for userinfo
            $atPos = strrpos($authority, '@');
            if ($atPos !== false) {
                $userInfo = substr($authority, 0, $atPos);
                $authority = substr($authority, $atPos + 1);
                $this->parseUserInfo($userInfo, $errors);
            }

            // Parse host and port
            $this->parseHostAndPort($authority, $errors);
        }

        /**
         * Parses a file: URL.
         */
        private function parseFileUrl(string $remaining, array &$errors): void
        {
            $this->scheme = 'file';

            // Expect //
            if (!str_starts_with($remaining, '//')) {
                if (str_starts_with($remaining, '/')) {
                    $remaining = substr($remaining, 1);
                } else {
                    $errors[] = new UrlValidationError(
                        $remaining,
                        UrlValidationErrorType::SpecialSchemeMissingFollowingSolidus,
                        false
                    );
                }
            } else {
                $remaining = substr($remaining, 2);
            }

            // Parse host and path
            $slashPos = strpos($remaining, '/');
            if ($slashPos !== false) {
                $hostPart = substr($remaining, 0, $slashPos);
                $pathPart = substr($remaining, $slashPos);
            } else {
                $hostPart = $remaining;
                $pathPart = '/';
            }

            // Handle Windows drive letter
            if (preg_match('/^[A-Za-z]:/', $pathPart)) {
                $this->path = '/' . $pathPart;
                $this->asciiHost = '';

                return;
            }

            if ($hostPart !== '') {
                $this->parseHost($hostPart, $errors);
            } else {
                $this->asciiHost = '';
            }

            $this->path = $this->parsePath($pathPart, $errors);
        }

        /**
         * Parses the host component.
         */
        private function parseHost(string $host, array &$errors): void
        {
            if ($host === '') {
                $this->asciiHost = '';
                $this->unicodeHost = '';

                return;
            }

            // IPv6
            if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
                $ipv6 = substr($host, 1, -1);
                if (!$this->isValidIpv6($ipv6)) {
                    $errors[] = new UrlValidationError(
                        $host,
                        UrlValidationErrorType::Ipv6InvalidCodePoint,
                        true
                    );

                    return;
                }
                $this->asciiHost = $host;
                $this->unicodeHost = $host;

                return;
            }

            // IPv4
            if ($this->isValidIpv4($host)) {
                $this->asciiHost = $host;
                $this->unicodeHost = $host;

                return;
            }

            // Domain - decode percent encoding
            $decodedHost = $this->decodePercent($host);

            // Check for invalid code points
            if (!$this->isValidHostCodePoints($decodedHost)) {
                $errors[] = new UrlValidationError(
                    $host,
                    UrlValidationErrorType::HostInvalidCodePoint,
                    true
                );

                return;
            }

            // IDNA conversion
            if (function_exists('idn_to_ascii')) {
                $ascii = @idn_to_ascii($decodedHost, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
                if ($ascii === false) {
                    $errors[] = new UrlValidationError(
                        $host,
                        UrlValidationErrorType::DomainToAscii,
                        true
                    );

                    return;
                }
                $this->asciiHost = $ascii;
            } else {
                $this->asciiHost = strtolower($decodedHost);
            }

            if (function_exists('idn_to_utf8')) {
                $unicode = @idn_to_utf8($this->asciiHost, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
                $this->unicodeHost = $unicode !== false ? $unicode : $this->asciiHost;
            } else {
                $this->unicodeHost = $this->asciiHost;
            }
        }

        /**
         * Parses host and port.
         */
        private function parseHostAndPort(string $hostPort, array &$errors): void
        {
            // Check for IPv6
            if (str_starts_with($hostPort, '[')) {
                $closeBracket = strpos($hostPort, ']');
                if ($closeBracket === false) {
                    $errors[] = new UrlValidationError(
                        $hostPort,
                        UrlValidationErrorType::Ipv6Unclosed,
                        true
                    );

                    return;
                }
                $host = substr($hostPort, 0, $closeBracket + 1);
                $remaining = substr($hostPort, $closeBracket + 1);

                if ($remaining !== '' && str_starts_with($remaining, ':')) {
                    $this->parsePort(substr($remaining, 1), $errors);
                }

                $this->parseHost($host, $errors);

                return;
            }

            // Find port
            $colonPos = strrpos($hostPort, ':');
            if ($colonPos !== false) {
                $possiblePort = substr($hostPort, $colonPos + 1);
                if (preg_match('/^\d*$/', $possiblePort)) {
                    $host = substr($hostPort, 0, $colonPos);
                    $this->parsePort($possiblePort, $errors);
                } else {
                    $host = $hostPort;
                }
            } else {
                $host = $hostPort;
            }

            $this->parseHost($host, $errors);
        }

        /**
         * Parses the path component.
         */
        private function parsePath(string $path, array &$errors): string
        {
            // Decode percent-encoded characters
            $decoded = $this->decodePercent($path);

            // Normalize the path (remove . and .. segments)
            return $this->normalizePath($decoded);
        }

        /**
         * Parses a path-only URL (no authority).
         */
        private function parsePathOnly(string $remaining, array &$errors): void
        {
            $fragmentPos = strpos($remaining, '#');
            if ($fragmentPos !== false) {
                $pathAndQuery = substr($remaining, 0, $fragmentPos);
                $this->fragment = substr($remaining, $fragmentPos + 1);
            } else {
                $pathAndQuery = $remaining;
            }

            $queryPos = strpos($pathAndQuery, '?');
            if ($queryPos !== false) {
                $this->path = $this->parsePath(substr($pathAndQuery, 0, $queryPos), $errors);
                $this->query = substr($pathAndQuery, $queryPos + 1);
            } else {
                $this->path = $this->parsePath($pathAndQuery, $errors);
            }
        }

        /**
         * Parses the port component.
         */
        private function parsePort(string $port, array &$errors): void
        {
            if ($port === '') {
                $this->port = null;

                return;
            }

            if (!ctype_digit($port)) {
                $errors[] = new UrlValidationError(
                    $port,
                    UrlValidationErrorType::PortInvalid,
                    true
                );

                return;
            }

            $portNum = (int)$port;
            if ($portNum > 65535) {
                $errors[] = new UrlValidationError(
                    $port,
                    UrlValidationErrorType::PortOutOfRange,
                    true
                );

                return;
            }

            $this->port = $portNum;
        }

        /**
         * Parses the URL string according to WHATWG URL Standard.
         *
         * @param string $url The URL to parse
         * @param self|null $baseUrl Base URL for resolution
         * @param array $errors Array to collect validation errors
         * @throws InvalidUrlException If parsing fails
         */
        private function parseUrl(string $url, ?self $baseUrl, array &$errors): void
        {
            // Trim whitespace
            $url = trim($url);

            // Remove ASCII tab or newline
            $url = str_replace(["\t", "\n", "\r"], '', $url);

            if ($url === '') {
                $errors[] = new UrlValidationError(
                    $url,
                    UrlValidationErrorType::MissingSchemeNonRelativeUrl,
                    true
                );

                return;
            }

            // Parse scheme
            $colonPos = strpos($url, ':');
            if ($colonPos !== false && $this->isValidScheme(substr($url, 0, $colonPos))) {
                $this->scheme = strtolower(substr($url, 0, $colonPos));
                $remaining = substr($url, $colonPos + 1);

                // Check for special schemes
                if ($this->isSpecialScheme()) {
                    // Expect // after :
                    if (!str_starts_with($remaining, '//') && !str_starts_with($remaining, '/')) {
                        $errors[] = new UrlValidationError(
                            $remaining,
                            UrlValidationErrorType::SpecialSchemeMissingFollowingSolidus,
                            false
                        );
                    }
                }

                // Handle file: scheme specially
                if ($this->scheme === 'file') {
                    $this->parseFileUrl($remaining, $errors);

                    return;
                }

                // Check for authority (//)
                if (str_starts_with($remaining, '//')) {
                    $this->parseUrlWithAuthority(substr($remaining, 2), $errors);
                } else {
                    // No authority - path only
                    $this->parsePathOnly($remaining, $errors);
                }
            } else {
                // No scheme - must have base URL
                if ($baseUrl === null) {
                    $errors[] = new UrlValidationError(
                        $url,
                        UrlValidationErrorType::MissingSchemeNonRelativeUrl,
                        true
                    );

                    return;
                }

                $this->resolveRelativeUrl($url, $baseUrl, $errors);
            }
        }

        /**
         * Parses a URL with authority component.
         */
        private function parseUrlWithAuthority(string $remaining, array &$errors): void
        {
            // Find the end of authority (next / ? #)
            $authorityEnd = $this->findAuthorityEnd($remaining);
            $authority = substr($remaining, 0, $authorityEnd);
            $remaining = substr($remaining, $authorityEnd);

            // Parse authority
            $this->parseAuthority($authority, $errors);

            // Parse path
            if (str_starts_with($remaining, '/')) {
                $pathEnd = $this->findPathEnd($remaining);
                $path = substr($remaining, 0, $pathEnd);
                $remaining = substr($remaining, $pathEnd);
                $this->path = $this->parsePath($path, $errors);
            } else {
                $this->path = '';
            }

            // Parse query
            if (str_starts_with($remaining, '?')) {
                $remaining = substr($remaining, 1);
                $fragmentPos = strpos($remaining, '#');
                if ($fragmentPos !== false) {
                    $this->query = substr($remaining, 0, $fragmentPos);
                    $remaining = substr($remaining, $fragmentPos);
                } else {
                    $this->query = $remaining;
                    $remaining = '';
                }
            }

            // Parse fragment
            if (str_starts_with($remaining, '#')) {
                $this->fragment = substr($remaining, 1);
            }
        }

        /**
         * Parses the userinfo component (username:password).
         */
        private function parseUserInfo(string $userInfo, array &$errors): void
        {
            $colonPos = strpos($userInfo, ':');
            if ($colonPos !== false) {
                $this->username = $this->decodePercent($this->trimTabNewline(substr($userInfo, 0, $colonPos)));
                $this->password = $this->decodePercent($this->trimTabNewline(substr($userInfo, $colonPos + 1)));
            } else {
                $this->username = $this->decodePercent($this->trimTabNewline($userInfo));
                $this->password = null;
            }
        }

        /**
         * Resolves a relative URL against a base URL.
         */
        private function resolveRelativeUrl(string $url, self $baseUrl, array &$errors): void
        {
            // Copy scheme from base
            $this->scheme = $baseUrl->scheme;

            // Check for scheme-relative URL
            if (str_starts_with($url, '//')) {
                $this->parseUrlWithAuthority(substr($url, 2), $errors);

                return;
            }

            // Check for absolute path
            if (str_starts_with($url, '/')) {
                $this->username = $baseUrl->username;
                $this->password = $baseUrl->password;
                $this->asciiHost = $baseUrl->asciiHost;
                $this->unicodeHost = $baseUrl->unicodeHost;
                $this->port = $baseUrl->port;
                $this->parsePathOnly($url, $errors);

                return;
            }

            // Check for query-only
            if (str_starts_with($url, '?')) {
                $this->username = $baseUrl->username;
                $this->password = $baseUrl->password;
                $this->asciiHost = $baseUrl->asciiHost;
                $this->unicodeHost = $baseUrl->unicodeHost;
                $this->port = $baseUrl->port;
                $this->path = $baseUrl->path;
                $fragmentPos = strpos($url, '#');
                if ($fragmentPos !== false) {
                    $this->query = substr($url, 1, $fragmentPos - 1);
                    $this->fragment = substr($url, $fragmentPos + 1);
                } else {
                    $this->query = substr($url, 1);
                }

                return;
            }

            // Check for fragment-only
            if (str_starts_with($url, '#')) {
                $this->username = $baseUrl->username;
                $this->password = $baseUrl->password;
                $this->asciiHost = $baseUrl->asciiHost;
                $this->unicodeHost = $baseUrl->unicodeHost;
                $this->port = $baseUrl->port;
                $this->path = $baseUrl->path;
                $this->query = $baseUrl->query;
                $this->fragment = substr($url, 1);

                return;
            }

            // Relative path
            $this->username = $baseUrl->username;
            $this->password = $baseUrl->password;
            $this->asciiHost = $baseUrl->asciiHost;
            $this->unicodeHost = $baseUrl->unicodeHost;
            $this->port = $baseUrl->port;

            // Remove the last segment of base path
            $basePath = $baseUrl->path;
            $lastSlash = strrpos($basePath, '/');
            if ($lastSlash !== false) {
                $basePath = substr($basePath, 0, $lastSlash + 1);
            } else {
                $basePath = '/';
            }

            $this->parsePathOnly($basePath . $url, $errors);
        }

        private function trimTabNewline(string $string): string
        {
            return str_replace(["\t", "\n", "\r"], '', $string);
        }
    }
}
