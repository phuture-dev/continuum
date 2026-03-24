<?php

declare(strict_types=1);

namespace Phuture\Continuum\Uri\Rfc3986;

use Uri\InvalidUriException;
use Uri\UriComparisonMode;

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
class Uri
{
    /**
     * Normalized fragment (decoded).
     *
     * @var string|null
     */
    private ?string $fragment = null;

    /**
     * Normalized host (lowercase).
     *
     * @var string|null
     */
    private ?string $host = null;

    /**
     * Normalized password (decoded).
     *
     * @var string|null
     */
    private ?string $password = null;

    /**
     * Normalized path (decoded).
     *
     * @var string
     */
    private string $path = '';

    /**
     * Port number, null if not specified or default.
     *
     * @var int|null
     */
    private ?int $port = null;

    /**
     * Normalized query string (decoded).
     *
     * @var string|null
     */
    private ?string $query = null;

    /**
     * Raw fragment with percent-encoding preserved.
     *
     * @var string|null
     */
    private ?string $rawFragment = null;

    /**
     * Raw host with percent-encoding preserved.
     *
     * @var string|null
     */
    private ?string $rawHost = null;

    /**
     * Raw password with percent-encoding preserved.
     *
     * @var string|null
     */
    private ?string $rawPassword = null;

    /**
     * Raw path with percent-encoding preserved.
     *
     * @var string
     */
    private string $rawPath = '';

    /**
     * Raw query string with percent-encoding preserved.
     *
     * @var string|null
     */
    private ?string $rawQuery = null;

    /**
     * Raw scheme as originally parsed.
     *
     * @var string|null
     */
    private ?string $rawScheme = null;

    /**
     * Raw userinfo with percent-encoding preserved.
     *
     * @var string|null
     */
    private ?string $rawUserInfo = null;

    /**
     * Raw username with percent-encoding preserved.
     *
     * @var string|null
     */
    private ?string $rawUsername = null;
    /**
     * Normalized scheme component (lowercase).
     *
     * @var string|null
     */
    private ?string $scheme = null;

    /**
     * Normalized userinfo (decoded).
     *
     * @var string|null
     */
    private ?string $userInfo = null;

    /**
     * Normalized username (decoded).
     *
     * @var string|null
     */
    private ?string $username = null;

    /**
     * Constructs a Uri instance from a URI string.
     *
     * @param string $uri The URI string to parse
     * @param self|null $baseUrl Optional base URI for resolving relative references
     * @throws InvalidUriException If the URI cannot be parsed
     */
    final public function __construct(string $uri, ?self $baseUrl = null)
    {
        // If a base URL is provided and the URI is relative, resolve it
        if ($baseUrl !== null && !preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $uri)) {
            $uri = $this->resolveRelative($uri, $baseUrl);
        }

        $this->parseUri($uri);
    }

    /**
     * Checks if this URI equals another URI.
     *
     * @param self $other The URI to compare with
     * @param string $mode Comparison mode (IncludeFragment or ExcludeFragment)
     * @return bool True if the URIs are equal according to the comparison mode
     */
    public function equals(self $other, string $mode = UriComparisonMode::ExcludeFragment): bool
    {
        if ($this->scheme !== $other->scheme) {
            return false;
        }
        if ($this->userInfo !== $other->userInfo) {
            return false;
        }
        if ($this->host !== $other->host) {
            return false;
        }
        if ($this->getPort() !== $other->getPort()) {
            return false;
        }
        if ($this->normalizePath($this->path) !== $this->normalizePath($other->path)) {
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

    /**
     * Returns the normalized fragment.
     *
     * @return string|null The decoded fragment, or null if not present
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * Returns the normalized host component.
     *
     * @return string|null The host in lowercase, or null if not present
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Returns the normalized password.
     *
     * @return string|null The decoded password, or null if not present
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Returns the normalized path component.
     *
     * @return string The decoded path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the port number.
     *
     * Returns null for default ports of known schemes (e.g., 80 for http, 443 for https).
     *
     * @return int|null The port number, or null if not specified or is default
     */
    public function getPort(): ?int
    {
        // Return null for default ports
        if ($this->port === null) {
            return null;
        }

        $defaultPorts = [
            'http' => 80,
            'https' => 443,
            'ftp' => 21,
            'ssh' => 22,
            'telnet' => 23,
            'smtp' => 25,
            'ldap' => 389,
            'ldaps' => 636,
            'imap' => 143,
            'imaps' => 993,
            'pop3' => 110,
            'pop3s' => 995,
        ];

        if (isset($defaultPorts[$this->scheme]) && $defaultPorts[$this->scheme] === $this->port) {
            return null;
        }

        return $this->port;
    }

    /**
     * Returns the normalized query string.
     *
     * @return string|null The decoded query string, or null if not present
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * Returns the raw fragment with percent-encoding preserved.
     *
     * @return string|null The raw fragment, or null if not present
     */
    public function getRawFragment(): ?string
    {
        return $this->rawFragment;
    }

    /**
     * Returns the raw host with percent-encoding preserved.
     *
     * @return string|null The raw host, or null if not present
     */
    public function getRawHost(): ?string
    {
        return $this->rawHost;
    }

    /**
     * Returns the raw password with percent-encoding preserved.
     *
     * @return string|null The raw password, or null if not present
     */
    public function getRawPassword(): ?string
    {
        return $this->rawPassword;
    }

    /**
     * Returns the raw path with percent-encoding preserved.
     *
     * @return string The raw path
     */
    public function getRawPath(): string
    {
        return $this->rawPath;
    }

    /**
     * Returns the raw query string with percent-encoding preserved.
     *
     * @return string|null The raw query string, or null if not present
     */
    public function getRawQuery(): ?string
    {
        return $this->rawQuery;
    }

    /**
     * Returns the raw scheme as originally parsed.
     *
     * @return string|null The raw scheme, or null if not present
     */
    public function getRawScheme(): ?string
    {
        return $this->rawScheme;
    }

    /**
     * Returns the raw userinfo with percent-encoding preserved.
     *
     * @return string|null The raw userinfo, or null if not present
     */
    public function getRawUserInfo(): ?string
    {
        return $this->rawUserInfo;
    }

    /**
     * Returns the raw username with percent-encoding preserved.
     *
     * @return string|null The raw username, or null if not present
     */
    public function getRawUsername(): ?string
    {
        return $this->rawUsername;
    }

    /**
     * Returns the normalized scheme component.
     *
     * @return string|null The scheme in lowercase, or null if not present
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * Returns the normalized userinfo component.
     *
     * @return string|null The decoded userinfo (username:password), or null if not present
     */
    public function getUserInfo(): ?string
    {
        return $this->userInfo;
    }

    /**
     * Returns the normalized username.
     *
     * @return string|null The decoded username, or null if not present
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Attempts to parse a URI string and returns a Uri instance or null on failure.
     *
     * @param string $uri The URI string to parse
     * @param self|null $baseUrl Optional base URI for resolving relative references
     * @return static|null Returns null if the URI cannot be parsed
     */
    public static function parse(string $uri, ?self $baseUrl = null): ?static
    {
        try {
            return new static($uri, $baseUrl);
        } catch (InvalidUriException $e) {
            return null;
        }
    }

    /**
     * Resolves a relative reference against this URI.
     *
     * @param string $reference The relative reference to resolve
     * @return static The resolved URI
     */
    public function resolve(string $reference): static
    {
        return new static($reference, $this);
    }

    /**
     * Returns the URI as a raw (percent-encoded) string.
     *
     * @return string The URI with percent-encoded components preserved
     */
    public function toRawString(): string
    {
        $result = '';

        if ($this->rawScheme !== null) {
            $result .= $this->rawScheme . ':';
        }

        if ($this->rawHost !== null) {
            $result .= '//';
            if ($this->rawUserInfo !== null) {
                $result .= $this->rawUserInfo . '@';
            }
            $result .= $this->rawHost;
            if ($this->port !== null && $this->getPort() !== null) {
                $result .= ':' . $this->port;
            }
        }

        $result .= $this->rawPath;

        if ($this->rawQuery !== null) {
            $result .= '?' . $this->rawQuery;
        }

        if ($this->rawFragment !== null) {
            $result .= '#' . $this->rawFragment;
        }

        return $result;
    }

    /**
     * Returns the URI as a decoded string.
     *
     * @return string The URI with decoded components
     */
    public function toString(): string
    {
        $result = '';

        if ($this->scheme !== null) {
            $result .= $this->scheme . ':';
        }

        if ($this->host !== null) {
            $result .= '//';
            if ($this->userInfo !== null) {
                $result .= $this->userInfo . '@';
            }
            $result .= $this->host;
            if ($this->port !== null && $this->getPort() !== null) {
                $result .= ':' . $this->port;
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

    /**
     * Returns a new instance with the specified fragment.
     *
     * @param string|null $fragment The new fragment (will be percent-encoded)
     * @return self A new Uri instance with the modified fragment
     */
    public function withFragment(?string $fragment): self
    {
        $new = clone $this;
        $new->fragment = $fragment;
        $new->rawFragment = $fragment !== null ? $this->encodeComponent($fragment) : null;

        return $new;
    }

    /**
     * Returns a new instance with the specified host.
     *
     * @param string|null $host The new host (will be normalized to lowercase)
     * @return self A new Uri instance with the modified host
     */
    public function withHost(?string $host): self
    {
        $new = clone $this;
        $new->host = $host !== null ? strtolower($host) : null;
        $new->rawHost = $host !== null ? $this->encodeComponent($host) : null;

        return $new;
    }

    /**
     * Returns a new instance with the specified path.
     *
     * @param string $path The new path (will be percent-encoded)
     * @return self A new Uri instance with the modified path
     */
    public function withPath(string $path): self
    {
        $new = clone $this;
        $new->path = $path;
        $new->rawPath = $this->encodeComponent($path, '/');

        return $new;
    }

    /**
     * Returns a new instance with the specified port.
     *
     * @param int|null $port The new port number, or null to remove
     * @return self A new Uri instance with the modified port
     */
    public function withPort(?int $port): self
    {
        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    /**
     * Returns a new instance with the specified query.
     *
     * @param string|null $query The new query string (will be percent-encoded)
     * @return self A new Uri instance with the modified query
     */
    public function withQuery(?string $query): self
    {
        $new = clone $this;
        $new->query = $query;
        $new->rawQuery = $query !== null ? $this->encodeComponent($query) : null;

        return $new;
    }

    /**
     * Returns a new instance with the specified scheme.
     *
     * @param string|null $scheme The new scheme (will be normalized to lowercase)
     * @return self A new Uri instance with the modified scheme
     */
    public function withScheme(?string $scheme): self
    {
        $new = clone $this;
        $new->scheme = $scheme !== null ? strtolower($scheme) : null;
        $new->rawScheme = $scheme;

        return $new;
    }

    /**
     * Returns a new instance with the specified user info.
     *
     * @param string|null $username The username (will be percent-encoded)
     * @param string|null $password The password (will be percent-encoded)
     * @return self A new Uri instance with the modified userinfo
     */
    public function withUserInfo(?string $username, ?string $password = null): self
    {
        $new = clone $this;

        if ($username === null) {
            $new->username = null;
            $new->rawUsername = null;
            $new->password = null;
            $new->rawPassword = null;
            $new->userInfo = null;
            $new->rawUserInfo = null;

            return $new;
        }

        if ($password === null) {
            $colonPos = strpos($username, ':');
            if ($colonPos !== false) {
                $password = substr($username, $colonPos + 1);
                $username = substr($username, 0, $colonPos);
            } else {
                $password = '';
            }
        }

        $new->username = $username;
        $new->rawUsername = $this->encodeComponent($username);
        $new->password = $password;
        $new->rawPassword = $password !== '' ? $this->encodeComponent($password) : '';

        $new->userInfo = $password !== '' ? $username . ':' . $password : $username;
        $new->rawUserInfo = $password !== ''
            ? $new->rawUsername . ':' . $new->rawPassword
            : $new->rawUsername;

        return $new;
    }

    /**
     * Builds a URI from base components and a new path.
     *
     * @param self $baseUrl The base URI to build from
     * @param string $path The path component to use
     * @return string The constructed URI string
     */
    private function buildUriFromBase(self $baseUrl, string $path): string
    {
        $result = '';
        if ($baseUrl->scheme !== null) {
            $result .= $baseUrl->scheme . ':';
        }
        if ($baseUrl->host !== null) {
            $result .= '//';
            if ($baseUrl->userInfo !== null) {
                $result .= $baseUrl->userInfo . '@';
            }
            $result .= $baseUrl->host;
            if ($baseUrl->port !== null) {
                $result .= ':' . $baseUrl->port;
            }
        }
        $result .= $path;

        return $result;
    }

    /**
     * Decodes a URI component, handling percent-encoded sequences.
     *
     * @param string $component The component to decode
     * @return string The decoded component
     */
    private function decodeComponent(string $component): string
    {
        return preg_replace_callback('/%([0-9A-Fa-f]{2})/', function ($match) {
            $byte = hexdec($match[1]);
            $char = chr($byte);
            // Only decode unreserved characters
            if (preg_match('/[A-Za-z0-9\-._~]/', $char)) {
                return $char;
            }

            return strtoupper($match[0]);
        }, $component);
    }

    /**
     * Decodes a host component.
     *
     * @param string $host The host to decode
     * @return string The decoded host
     */
    private function decodeHost(string $host): string
    {
        // Check if it's an IPv6 address
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            return $host; // Keep IPv6 addresses as-is
        }

        return $this->decodeComponent($host);
    }

    /**
     * Encodes a URI component according to RFC 3986 rules.
     *
     * @param string $component The component to encode
     * @param string $additionalUnreserved Additional characters to leave unencoded
     * @return string The percent-encoded component
     */
    private function encodeComponent(string $component, string $additionalUnreserved = ''): string
    {
        $result = '';
        $length = strlen($component);

        // phpcs:ignore
        for ($i = 0; $i < $length; $i++) {
            $char = $component[$i];
            $ord = ord($char);

            // Unreserved characters
            if (
                ($ord >= 65 && $ord <= 90) ||  // A-Z
                ($ord >= 97 && $ord <= 122) || // a-z
                ($ord >= 48 && $ord <= 57) ||  // 0-9
                $char === '-' || $char === '.' || $char === '_' || $char === '~' ||
                str_contains($additionalUnreserved, $char)
            ) {
                $result .= $char;
            } else {
                $result .= sprintf('%%%02X', $ord);
            }
        }

        return $result;
    }

    /**
     * Normalizes a path by removing dot segments.
     *
     * @param string $path The path to normalize
     * @return string The normalized path
     */
    private function normalizePath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        $result = [];
        $segments = explode('/', $path);
        $isAbsolute = str_starts_with($path, '/');
        $hasTrailingSlash = str_ends_with($path, '/');

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                if (!empty($result)) {
                    array_pop($result);
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
     * Parses the URI string into components.
     *
     * @param string $uri The URI string to parse
     * @throws InvalidUriException If the URI cannot be parsed
     */
    private function parseUri(string $uri): void
    {
        if (str_starts_with($uri, '://')) {
            throw new InvalidUriException('Failed to parse URI: ' . $uri);
        }

        $components = parse_url($uri);

        if ($components === false) {
            throw new InvalidUriException('Failed to parse URI: ' . $uri);
        }

        // Store raw components
        $this->rawScheme = $components['scheme'] ?? null;
        $this->rawHost = $components['host'] ?? null;
        $this->rawPath = $components['path'] ?? '';
        $this->rawQuery = $components['query'] ?? null;
        $this->rawFragment = $components['fragment'] ?? null;

        // Parse and decode components
        if (isset($components['scheme'])) {
            $this->scheme = strtolower($components['scheme']);
        }

        if (isset($components['host'])) {
            $this->host = strtolower($this->decodeHost($components['host']));
        }

        $this->port = $components['port'] ?? null;

        // Parse user info
        if (isset($components['user'])) {
            $this->rawUsername = $components['user'];
            $this->username = $this->decodeComponent($components['user']);

            if (isset($components['pass'])) {
                $this->rawPassword = $components['pass'];
                $this->password = $this->decodeComponent($components['pass']);
                $this->rawUserInfo = $components['user'] . ':' . $components['pass'];
                $this->userInfo = $this->username . ':' . $this->password;
            } else {
                $this->rawUserInfo = $components['user'];
                $this->userInfo = $this->username;
            }
        }

        $this->path = $this->decodeComponent($components['path'] ?? '');
        $this->rawPath = $components['path'] ?? '';

        if (isset($components['query'])) {
            $this->query = $this->decodeComponent($components['query']);
            $this->rawQuery = $components['query'];
        }

        if (isset($components['fragment'])) {
            $this->fragment = $this->decodeComponent($components['fragment']);
            $this->rawFragment = $components['fragment'];
        }
    }

    /**
     * Removes dot segments from a path per RFC 3986 Section 5.2.4.
     *
     * @param string $path The path to normalize
     * @return string The path with dot segments removed
     */
    private function removeDotSegments(string $path): string
    {
        $result = [];
        $segments = explode('/', $path);
        $isAbsolute = str_starts_with($path, '/');
        $hasTrailingSlash = str_ends_with($path, '/') && strlen($path) > 1;

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                if (!empty($result)) {
                    array_pop($result);
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
     * Resolves a relative URI against a base URI.
     *
     * @param string $uri The relative URI to resolve
     * @param self $baseUrl The base URI to resolve against
     * @return string The resolved absolute URI
     */
    private function resolveRelative(string $uri, self $baseUrl): string
    {
        // Network-path reference (starts with //)
        if (str_starts_with($uri, '//')) {
            return ($baseUrl->scheme !== null ? $baseUrl->scheme . ':' : '') . $uri;
        }

        // Absolute-path reference (starts with /)
        if (str_starts_with($uri, '/')) {
            return $this->buildUriFromBase($baseUrl, $uri);
        }

        // Same-document reference (empty or fragment only)
        if ($uri === '' || str_starts_with($uri, '#')) {
            $base = $baseUrl->toString();
            if (str_contains($base, '#')) {
                $base = substr($base, 0, strpos($base, '#'));
            }

            return $base . $uri;
        }

        // Query-only reference
        if (str_starts_with($uri, '?')) {
            $base = $baseUrl->toString();
            if (str_contains($base, '?')) {
                $base = substr($base, 0, strpos($base, '?'));
            } elseif (str_contains($base, '#')) {
                $base = substr($base, 0, strpos($base, '#'));
            }

            return $base . $uri;
        }

        // Relative-path reference
        $basePath = $baseUrl->path;
        $lastSlash = strrpos($basePath, '/');
        if ($lastSlash !== false) {
            $resolvedPath = substr($basePath, 0, $lastSlash + 1) . $uri;
        } else {
            $resolvedPath = $uri;
        }

        // Remove dot segments
        $resolvedPath = $this->removeDotSegments($resolvedPath);

        return $this->buildUriFromBase($baseUrl, $resolvedPath);
    }
}
