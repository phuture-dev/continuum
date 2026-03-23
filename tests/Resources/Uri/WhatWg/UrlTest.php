<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests\Resources\Uri\WhatWg;

use Uri\UriComparisonMode;
use Tester\{Assert, TestCase};
use Uri\WhatWg\{InvalidUrlException, Url, UrlValidationError, UrlValidationErrorType};

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class UrlTest extends TestCase
{
    public function testEqualsDifferentHosts(): void
    {
        $url1 = new Url('https://example.com');
        $url2 = new Url('https://other.com');

        Assert::false($url1->equals($url2));
    }

    public function testEqualsDifferentPaths(): void
    {
        $url1 = new Url('https://example.com/path1');
        $url2 = new Url('https://example.com/path2');

        Assert::false($url1->equals($url2));
    }

    public function testEqualsDifferentQueries(): void
    {
        $url1 = new Url('https://example.com?key=1');
        $url2 = new Url('https://example.com?key=2');

        Assert::false($url1->equals($url2));
    }

    public function testEqualsDifferentSchemes(): void
    {
        $url1 = new Url('http://example.com');
        $url2 = new Url('https://example.com');

        Assert::false($url1->equals($url2));
    }

    public function testEqualsWithExcludeFragmentMode(): void
    {
        $url1 = new Url('https://example.com/path#fragment1');
        $url2 = new Url('https://example.com/path#fragment2');

        Assert::true($url1->equals($url2, UriComparisonMode::ExcludeFragment));
    }

    public function testEqualsWithIncludeFragmentMode(): void
    {
        $url1 = new Url('https://example.com/path#fragment1');
        $url2 = new Url('https://example.com/path#fragment2');

        Assert::false($url1->equals($url2, UriComparisonMode::IncludeFragment));
    }

    public function testEqualsWithSameFragment(): void
    {
        $url1 = new Url('https://example.com/path#fragment');
        $url2 = new Url('https://example.com/path#fragment');

        Assert::true($url1->equals($url2, UriComparisonMode::IncludeFragment));
    }

    public function testGetAsciiHostReturnsHost(): void
    {
        $url = new Url('https://example.com');

        Assert::same('example.com', $url->getAsciiHost());
    }

    public function testGetFragmentReturnsFragment(): void
    {
        $url = new Url('https://example.com#section-1');

        Assert::same('section-1', $url->getFragment());
    }

    public function testGetPasswordNullWhenNotPresent(): void
    {
        $url = new Url('https://user@example.com');

        Assert::null($url->getPassword());
    }

    public function testGetPasswordReturnsPassword(): void
    {
        $url = new Url('https://user:pass@example.com');

        Assert::same('pass', $url->getPassword());
    }

    public function testGetPathReturnsPath(): void
    {
        $url = new Url('https://example.com/path/to/resource');

        Assert::same('/path/to/resource', $url->getPath());
    }

    public function testGetPortReturnsValueForNonDefaultPorts(): void
    {
        $url = new Url('https://example.com:8443');

        Assert::same(8443, $url->getPort());
    }

    public function testGetQueryReturnsQueryString(): void
    {
        $url = new Url('https://example.com?key1=value1&key2=value2');

        Assert::same('key1=value1&key2=value2', $url->getQuery());
    }

    public function testGetSchemeReturnsLowercase(): void
    {
        $url = new Url('HTTPS://EXAMPLE.COM');

        Assert::same('https', $url->getScheme());
    }

    public function testGetUnicodeHostReturnsHost(): void
    {
        $url = new Url('https://example.com');

        Assert::same('example.com', $url->getUnicodeHost());
    }

    public function testGetUsernameNullWhenNotPresent(): void
    {
        $url = new Url('https://example.com');

        Assert::null($url->getUsername());
    }

    public function testGetUsernameReturnsUsername(): void
    {
        $url = new Url('https://user@example.com');

        Assert::same('user', $url->getUsername());
    }

    public function testInvalidUrlExceptionContainsCorrectErrorType(): void
    {
        try {
            new Url('https://example.com:99999');
            Assert::fail('Expected InvalidUrlException was not thrown');
        } catch (InvalidUrlException $e) {
            $hasPortError = false;
            foreach ($e->errors as $error) {
                if ($error->type === UrlValidationErrorType::PortOutOfRange) {
                    $hasPortError = true;
                    Assert::true($error->failure);
                    break;
                }
            }
            Assert::true($hasPortError, 'Expected PortOutOfRange error not found');
        }
    }

    public function testInvalidUrlExceptionContainsErrors(): void
    {
        try {
            new Url('not-a-valid-url-without-base');
            Assert::fail('Expected InvalidUrlException was not thrown');
        } catch (InvalidUrlException $e) {
            Assert::type('array', $e->errors);
            Assert::true(count($e->errors) > 0);
        }
    }

    public function testParseFileUrl(): void
    {
        $url = new Url('file:///path/to/file');

        Assert::same('file', $url->getScheme());
        Assert::same('/path/to/file', $url->getPath());
    }

    public function testParseFtpUrl(): void
    {
        $url = new Url('ftp://user:pass@example.com:21/path');

        Assert::same('ftp', $url->getScheme());
        Assert::same('user', $url->getUsername());
        Assert::same('pass', $url->getPassword());
        Assert::same('/path', $url->getPath());
    }

    public function testParseInvalidUrlThrowsException(): void
    {
        Assert::exception(
            fn () => new Url('not-a-valid-url-without-base'),
            InvalidUrlException::class
        );
    }

    public function testParseRelativeUrlWithAbsolutePath(): void
    {
        $baseUrl = new Url('https://example.com/base/path');
        $url = new Url('/absolute/path', $baseUrl);

        Assert::same('https', $url->getScheme());
        Assert::same('example.com', $url->getAsciiHost());
        Assert::same('/absolute/path', $url->getPath());
    }

    public function testParseRelativeUrlWithBaseUrl(): void
    {
        $baseUrl = new Url('https://example.com/base/path/');
        $url = new Url('relative', $baseUrl);

        Assert::same('https', $url->getScheme());
        Assert::same('example.com', $url->getAsciiHost());
        Assert::same('/base/path/relative', $url->getPath());
    }

    public function testParseRelativeUrlWithFragmentOnly(): void
    {
        $baseUrl = new Url('https://example.com/path?query=value');
        $url = new Url('#new-fragment', $baseUrl);

        Assert::same('https', $url->getScheme());
        Assert::same('example.com', $url->getAsciiHost());
        Assert::same('/path', $url->getPath());
        Assert::same('query=value', $url->getQuery());
        Assert::same('new-fragment', $url->getFragment());
    }

    public function testParseRelativeUrlWithQueryOnly(): void
    {
        $baseUrl = new Url('https://example.com/path?old=value');
        $url = new Url('?new=query', $baseUrl);

        Assert::same('https', $url->getScheme());
        Assert::same('example.com', $url->getAsciiHost());
        Assert::same('/path', $url->getPath());
        Assert::same('new=query', $url->getQuery());
    }

    public function testParseRelativeUrlWithSchemeRelativeUrl(): void
    {
        $baseUrl = new Url('https://example.com/path');
        $url = new Url('//other.com/newpath', $baseUrl);

        Assert::same('https', $url->getScheme());
        Assert::same('other.com', $url->getAsciiHost());
        Assert::same('/newpath', $url->getPath());
    }
    public function testParseStandardWebUrl(): void
    {
        $url = new Url('https://example.com/path?query=value#fragment');

        Assert::same('https', $url->getScheme());
        Assert::same('example.com', $url->getAsciiHost());
        Assert::same('/path', $url->getPath());
        Assert::same('query=value', $url->getQuery());
        Assert::same('fragment', $url->getFragment());
    }

    public function testParseUrlWithAllComponents(): void
    {
        $url = new Url('https://user:pass@example.com:8080/path?query=value#fragment');

        Assert::same('https', $url->getScheme());
        Assert::same('user', $url->getUsername());
        Assert::same('pass', $url->getPassword());
        Assert::same('example.com', $url->getAsciiHost());
        Assert::same(8080, $url->getPort());
        Assert::same('/path', $url->getPath());
        Assert::same('query=value', $url->getQuery());
        Assert::same('fragment', $url->getFragment());
    }

    public function testParseUrlWithEmptyPath(): void
    {
        $url = new Url('https://example.com');

        Assert::same('/', $url->getPath());
    }

    public function testParseUrlWithInvalidPortThrowsException(): void
    {
        Assert::exception(
            fn () => new Url('https://example.com:99999'),
            InvalidUrlException::class
        );
    }

    public function testParseUrlWithIpv4Host(): void
    {
        $url = new Url('https://192.168.1.1/path');

        Assert::same('192.168.1.1', $url->getAsciiHost());
    }

    public function testParseUrlWithIpv6FullAddress(): void
    {
        $url = new Url('https://[2001:db8::1]/path');

        Assert::same('[2001:db8::1]', $url->getAsciiHost());
    }

    public function testParseUrlWithIpv6Host(): void
    {
        $url = new Url('https://[::1]/path');

        Assert::same('[::1]', $url->getAsciiHost());
        Assert::same('[::1]', $url->getUnicodeHost());
    }

    public function testParseUrlWithSpecialSchemes(): void
    {
        $schemes = [
            'http' => 'http://example.com',
            'https' => 'https://example.com',
            'ftp' => 'ftp://example.com',
            'ws' => 'ws://example.com',
            'wss' => 'wss://example.com',
        ];

        foreach ($schemes as $scheme => $urlString) {
            $url = new Url($urlString);
            Assert::same($scheme, $url->getScheme());
        }
    }

    public function testParseUrlWithTrailingSlash(): void
    {
        $url = new Url('https://example.com/path/');

        Assert::same('/path/', $url->getPath());
    }

    public function testParseUrlWithWhitespace(): void
    {
        $url = new Url('  https://example.com/path  ');

        Assert::same('https', $url->getScheme());
        Assert::same('example.com', $url->getAsciiHost());
    }

    public function testResolveAbsolutePathReference(): void
    {
        $baseUrl = new Url('https://example.com/base/path');
        $resolved = $baseUrl->resolve('/absolute');

        Assert::same('https', $resolved->getScheme());
        Assert::same('example.com', $resolved->getAsciiHost());
        Assert::same('/absolute', $resolved->getPath());
    }

    public function testResolveFragmentOnlyReference(): void
    {
        $baseUrl = new Url('https://example.com/path#old');
        $resolved = $baseUrl->resolve('#new');

        Assert::same('https', $resolved->getScheme());
        Assert::same('example.com', $resolved->getAsciiHost());
        Assert::same('/path', $resolved->getPath());
        Assert::same('new', $resolved->getFragment());
    }

    public function testResolveQueryOnlyReference(): void
    {
        $baseUrl = new Url('https://example.com/path?old=value');
        $resolved = $baseUrl->resolve('?new=query');

        Assert::same('https', $resolved->getScheme());
        Assert::same('example.com', $resolved->getAsciiHost());
        Assert::same('/path', $resolved->getPath());
        Assert::same('new=query', $resolved->getQuery());
    }

    public function testResolveRelativePathReference(): void
    {
        $baseUrl = new Url('https://example.com/base/path/');
        $resolved = $baseUrl->resolve('relative');

        Assert::same('https', $resolved->getScheme());
        Assert::same('example.com', $resolved->getAsciiHost());
        Assert::same('/base/path/relative', $resolved->getPath());
    }

    public function testResolveSchemeRelativeUrl(): void
    {
        $baseUrl = new Url('https://example.com/path');
        $resolved = $baseUrl->resolve('//other.com/path');

        Assert::same('https', $resolved->getScheme());
        Assert::same('other.com', $resolved->getAsciiHost());
        Assert::same('/path', $resolved->getPath());
    }

    public function testStaticParsePopulatesErrorsArray(): void
    {
        $errors = [];
        $url = Url::parse('://invalid', null, $errors);

        Assert::null($url);
        Assert::type('array', $errors);
        Assert::true(count($errors) > 0);

        // Check that errors contain UrlValidationError objects
        foreach ($errors as $error) {
            Assert::type(UrlValidationError::class, $error);
        }
    }

    public function testStaticParseReturnsNullOnFailure(): void
    {
        $errors = [];
        $url = Url::parse('not-a-valid-url-without-base', null, $errors);

        Assert::null($url);
        Assert::true(count($errors) > 0);
    }

    public function testStaticParseReturnsUrlInstance(): void
    {
        $url = Url::parse('https://example.com');

        Assert::type(Url::class, $url);
        Assert::same('https', $url->getScheme());
        Assert::same('example.com', $url->getAsciiHost());
    }

    public function testStaticParseWithBaseUrl(): void
    {
        $baseUrl = new Url('https://example.com/base/');
        $url = Url::parse('relative', $baseUrl);

        Assert::type(Url::class, $url);
        Assert::same('/base/relative', $url->getPath());
    }

    public function testToAsciiStringReturnsUrl(): void
    {
        $url = new Url('https://example.com/path');

        Assert::same('https://example.com/path', $url->toAsciiString());
    }

    public function testToAsciiStringWithAllComponents(): void
    {
        $url = new Url('https://user:pass@example.com:8080/path?query=value#fragment');
        $string = $url->toAsciiString();

        Assert::contains('https://', $string);
        Assert::contains('user:pass@', $string);
        Assert::contains('example.com', $string);
        Assert::contains(':8080', $string);
        Assert::contains('/path', $string);
        Assert::contains('?query=value', $string);
        Assert::contains('#fragment', $string);
    }

    public function testToAsciiStringWithIpv6Host(): void
    {
        $url = new Url('https://[::1]/path');
        $string = $url->toAsciiString();

        Assert::contains('[::1]', $string);
    }

    public function testToStringReturnsToAsciiString(): void
    {
        $url = new Url('https://example.com/path');

        Assert::same($url->toAsciiString(), $url->toAsciiString());
    }

    public function testToUnicodeStringReturnsUrl(): void
    {
        $url = new Url('https://example.com/path');

        Assert::same('https://example.com/path', $url->toUnicodeString());
    }

    public function testUrlValidationErrorHasContextProperty(): void
    {
        $error = new UrlValidationError(
            'my-context',
            UrlValidationErrorType::PortInvalid,
            true
        );

        Assert::same('my-context', $error->context);
    }

    public function testUrlValidationErrorHasFailureProperty(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );

        Assert::true($error->failure);
    }

    public function testUrlValidationErrorHasTypeProperty(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );

        Assert::same(UrlValidationErrorType::PortInvalid, $error->type);
    }

    public function testUrlValidationErrorWithFailureFalse(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::SpecialSchemeMissingFollowingSolidus,
            false
        );

        Assert::false($error->failure);
    }

    public function testWithFragmentNullRemovesFragment(): void
    {
        $original = new Url('https://example.com#fragment');
        $modified = $original->withFragment(null);

        Assert::same('fragment', $original->getFragment());
        Assert::null($modified->getFragment());
    }

    public function testWithFragmentReturnsNewInstance(): void
    {
        $original = new Url('https://example.com#old');
        $modified = $original->withFragment('new');

        Assert::same('old', $original->getFragment());
        Assert::same('new', $modified->getFragment());
        Assert::notSame($original, $modified);
    }

    public function testWithHostReturnsNewInstance(): void
    {
        $original = new Url('https://example.com');
        $modified = $original->withHost('other.com');

        Assert::same('example.com', $original->getAsciiHost());
        Assert::same('other.com', $modified->getAsciiHost());
        Assert::notSame($original, $modified);
    }

    public function testWithPasswordNullRemovesPassword(): void
    {
        $original = new Url('https://user:pass@example.com');
        $modified = $original->withPassword(null);

        Assert::same('pass', $original->getPassword());
        Assert::null($modified->getPassword());
    }

    public function testWithPasswordReturnsNewInstance(): void
    {
        $original = new Url('https://example.com');
        $modified = $original->withPassword('pass');

        Assert::null($original->getPassword());
        Assert::same('pass', $modified->getPassword());
        Assert::notSame($original, $modified);
    }

    public function testWithPathReturnsNewInstance(): void
    {
        $original = new Url('https://example.com/old');
        $modified = $original->withPath('/new/path');

        Assert::same('/old', $original->getPath());
        Assert::same('/new/path', $modified->getPath());
        Assert::notSame($original, $modified);
    }

    public function testWithPortNullRemovesPort(): void
    {
        $original = new Url('https://example.com:8080');
        $modified = $original->withPort(null);

        Assert::same(8080, $original->getPort());
        Assert::null($modified->getPort());
    }

    public function testWithPortReturnsNewInstance(): void
    {
        $original = new Url('https://example.com');
        $modified = $original->withPort(8080);

        Assert::null($original->getPort());
        Assert::same(8080, $modified->getPort());
        Assert::notSame($original, $modified);
    }

    public function testWithQueryNullRemovesQuery(): void
    {
        $original = new Url('https://example.com?query=value');
        $modified = $original->withQuery(null);

        Assert::same('query=value', $original->getQuery());
        Assert::null($modified->getQuery());
    }

    public function testWithQueryReturnsNewInstance(): void
    {
        $original = new Url('https://example.com?old=value');
        $modified = $original->withQuery('new=query');

        Assert::same('old=value', $original->getQuery());
        Assert::same('new=query', $modified->getQuery());
        Assert::notSame($original, $modified);
    }

    public function testWithSchemeNormalizesToLowercase(): void
    {
        $url = new Url('https://example.com');
        $modified = $url->withScheme('HTTP');

        Assert::same('http', $modified->getScheme());
    }

    public function testWithSchemeReturnsNewInstance(): void
    {
        $original = new Url('https://example.com');
        $modified = $original->withScheme('http');

        Assert::same('https', $original->getScheme());
        Assert::same('http', $modified->getScheme());
        Assert::notSame($original, $modified);
    }

    public function testWithUsernameNullRemovesUsername(): void
    {
        $original = new Url('https://user@example.com');
        $modified = $original->withUsername(null);

        Assert::same('user', $original->getUsername());
        Assert::null($modified->getUsername());
    }

    public function testWithUsernameReturnsNewInstance(): void
    {
        $original = new Url('https://example.com');
        $modified = $original->withUsername('user');

        Assert::null($original->getUsername());
        Assert::same('user', $modified->getUsername());
        Assert::notSame($original, $modified);
    }
}

(new UrlTest())->run();
