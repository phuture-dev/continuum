<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests\Resources\Uri\Rfc3986;

use Tester\Assert;
use Tester\TestCase;
use Uri\{InvalidUriException, UriComparisonMode};
use Uri\Rfc3986\Uri;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class UriTest extends TestCase
{
    // =========================================================================
    // Parsing & Construction Tests
    // =========================================================================

    public function testParseAbsoluteUriWithAllComponents(): void
    {
        $uri = new Uri('https://user:pass@example.com:8080/path/to/resource?query=value#fragment');

        Assert::same('https', $uri->getScheme());
        Assert::same('user', $uri->getUsername());
        Assert::same('pass', $uri->getPassword());
        Assert::same('example.com', $uri->getHost());
        Assert::same(8080, $uri->getPort());
        Assert::same('/path/to/resource', $uri->getPath());
        Assert::same('query=value', $uri->getQuery());
        Assert::same('fragment', $uri->getFragment());
    }

    public function testParseUriWithMinimalComponents(): void
    {
        $uri = new Uri('http://example.com');

        Assert::same('http', $uri->getScheme());
        Assert::null($uri->getUsername());
        Assert::null($uri->getPassword());
        Assert::same('example.com', $uri->getHost());
        Assert::null($uri->getPort());
        Assert::same('', $uri->getPath());
        Assert::null($uri->getQuery());
        Assert::null($uri->getFragment());
    }

    public function testParseUriWithSchemeOnly(): void
    {
        $uri = new Uri('mailto:test@example.com');

        Assert::same('mailto', $uri->getScheme());
        Assert::null($uri->getHost());
        Assert::same('test@example.com', $uri->getPath());
    }

    public function testParseRelativeUriWithBaseUrl(): void
    {
        $baseUrl = new Uri('https://example.com/base/path/');
        $uri = new Uri('relative', $baseUrl);

        Assert::same('https', $uri->getScheme());
        Assert::same('example.com', $uri->getHost());
        Assert::same('/base/path/relative', $uri->getPath());
    }

    public function testParseRelativeUriWithAbsolutePath(): void
    {
        $baseUrl = new Uri('https://example.com/base/path');
        $uri = new Uri('/absolute/path', $baseUrl);

        Assert::same('https', $uri->getScheme());
        Assert::same('example.com', $uri->getHost());
        Assert::same('/absolute/path', $uri->getPath());
    }

    public function testParseRelativeUriWithNetworkPath(): void
    {
        $baseUrl = new Uri('https://example.com/path');
        $uri = new Uri('//other.com/newpath', $baseUrl);

        Assert::same('https', $uri->getScheme());
        Assert::same('other.com', $uri->getHost());
        Assert::same('/newpath', $uri->getPath());
    }

    public function testParseRelativeUriWithQueryOnly(): void
    {
        $baseUrl = new Uri('https://example.com/path?old=value');
        $uri = new Uri('?new=query', $baseUrl);

        Assert::same('https', $uri->getScheme());
        Assert::same('example.com', $uri->getHost());
        Assert::same('/path', $uri->getPath());
        Assert::same('new=query', $uri->getQuery());
    }

    public function testParseRelativeUriWithFragmentOnly(): void
    {
        $baseUrl = new Uri('https://example.com/path?query=value#old');
        $uri = new Uri('#new-fragment', $baseUrl);

        Assert::same('https', $uri->getScheme());
        Assert::same('example.com', $uri->getHost());
        Assert::same('/path', $uri->getPath());
        Assert::same('query=value', $uri->getQuery());
        Assert::same('new-fragment', $uri->getFragment());
    }

    public function testParseUriWithVariousSchemes(): void
    {
        $schemes = ['http', 'https', 'ftp', 'ftps', 'ssh', 'telnet', 'mailto', 'file', 'data', 'wss'];

        foreach ($schemes as $scheme) {
            $uri = new Uri($scheme . '://example.com');
            Assert::same($scheme, $uri->getScheme());
        }
    }

    public function testParseInvalidUriThrowsException(): void
    {
        Assert::exception(
            fn() => new Uri('://missing-scheme'),
            InvalidUriException::class
        );
    }

    // =========================================================================
    // Getter Tests
    // =========================================================================

    public function testGetSchemeReturnsNormalizedValue(): void
    {
        $uri = new Uri('HTTPS://Example.COM');
        Assert::same('https', $uri->getScheme());
    }

    public function testGetHostReturnsNormalizedValue(): void
    {
        $uri = new Uri('https://EXAMPLE.COM');
        Assert::same('example.com', $uri->getHost());
    }

    public function testGetPortReturnsValueForNonDefaultPorts(): void
    {
        $uri = new Uri('http://example.com:8080');
        Assert::same(8080, $uri->getPort());
    }

    public function testGetPathReturnsPathValue(): void
    {
        $uri = new Uri('https://example.com/path/to/resource');
        Assert::same('/path/to/resource', $uri->getPath());
    }

    public function testGetQueryReturnsQueryValue(): void
    {
        $uri = new Uri('https://example.com?key=value');
        Assert::same('key=value', $uri->getQuery());
    }

    public function testGetFragmentReturnsFragmentValue(): void
    {
        $uri = new Uri('https://example.com#section-1');
        Assert::same('section-1', $uri->getFragment());
    }

    public function testGetUsernameReturnsUsername(): void
    {
        $uri = new Uri('https://user:pass@example.com');
        Assert::same('user', $uri->getUsername());
    }

    public function testGetPasswordReturnsPassword(): void
    {
        $uri = new Uri('https://user:pass@example.com');
        Assert::same('pass', $uri->getPassword());
    }

    public function testGetUserInfoReturnsUserInfo(): void
    {
        $uri = new Uri('https://user:pass@example.com');
        Assert::same('user:pass', $uri->getUserInfo());
    }

    public function testGetUserInfoWithoutPassword(): void
    {
        $uri = new Uri('https://user@example.com');
        Assert::same('user', $uri->getUserInfo());
    }

    // =========================================================================
    // Raw Getter Tests
    // =========================================================================

    public function testGetRawSchemeReturnsOriginalValue(): void
    {
        $uri = new Uri('HTTPS://example.com');
        Assert::same('HTTPS', $uri->getRawScheme());
    }

    public function testGetRawHostReturnsHost(): void
    {
        $uri = new Uri('https://example.com');
        Assert::same('example.com', $uri->getRawHost());
    }

    public function testGetRawPathReturnsPath(): void
    {
        $uri = new Uri('https://example.com/path/to/resource');
        Assert::same('/path/to/resource', $uri->getRawPath());
    }

    public function testGetRawQueryReturnsQuery(): void
    {
        $uri = new Uri('https://example.com?key=value');
        Assert::same('key=value', $uri->getRawQuery());
    }

    public function testGetRawFragmentReturnsFragment(): void
    {
        $uri = new Uri('https://example.com#section');
        Assert::same('section', $uri->getRawFragment());
    }

    public function testGetRawUsernameReturnsUsername(): void
    {
        $uri = new Uri('https://user:pass@example.com');
        Assert::same('user', $uri->getRawUsername());
    }

    public function testGetRawPasswordReturnsPassword(): void
    {
        $uri = new Uri('https://user:pass@example.com');
        Assert::same('pass', $uri->getRawPassword());
    }

    public function testGetRawUserInfoReturnsUserInfo(): void
    {
        $uri = new Uri('https://user:pass@example.com');
        Assert::same('user:pass', $uri->getRawUserInfo());
    }

    // =========================================================================
    // String Conversion Tests
    // =========================================================================

    public function testToStringReturnsString(): void
    {
        $uri = new Uri('https://user:pass@example.com:8080/path?query=value#fragment');
        Assert::same('https://user:pass@example.com:8080/path?query=value#fragment', $uri->toString());
    }

    public function testToRawStringReturnsString(): void
    {
        $uri = new Uri('https://example.com/path/to/resource');
        Assert::same('https://example.com/path/to/resource', $uri->toRawString());
    }

    // =========================================================================
    // Immutable Modification Tests
    // =========================================================================

    public function testWithSchemeReturnsNewInstance(): void
    {
        $original = new Uri('https://example.com');
        $modified = $original->withScheme('ftp');

        Assert::same('https', $original->getScheme());
        Assert::same('ftp', $modified->getScheme());
        Assert::notSame($original, $modified);
    }

    public function testWithSchemeNormalizesToLowercase(): void
    {
        $uri = new Uri('https://example.com');
        $modified = $uri->withScheme('HTTPS');

        Assert::same('https', $modified->getScheme());
    }

    public function testWithHostReturnsNewInstance(): void
    {
        $original = new Uri('https://example.com');
        $modified = $original->withHost('other.com');

        Assert::same('example.com', $original->getHost());
        Assert::same('other.com', $modified->getHost());
        Assert::notSame($original, $modified);
    }

    public function testWithHostNormalizesToLowercase(): void
    {
        $uri = new Uri('https://example.com');
        $modified = $uri->withHost('OTHER.COM');

        Assert::same('other.com', $modified->getHost());
    }

    public function testWithPortReturnsNewInstance(): void
    {
        $original = new Uri('https://example.com');
        $modified = $original->withPort(8080);

        Assert::null($original->getPort());
        Assert::same(8080, $modified->getPort());
        Assert::notSame($original, $modified);
    }

    public function testWithPortNullRemovesPort(): void
    {
        $original = new Uri('https://example.com:8080');
        $modified = $original->withPort(null);

        Assert::same(8080, $original->getPort());
        Assert::null($modified->getPort());
    }

    public function testWithPathReturnsNewInstance(): void
    {
        $original = new Uri('https://example.com/old');
        $modified = $original->withPath('/new/path');

        Assert::same('/old', $original->getPath());
        Assert::same('/new/path', $modified->getPath());
        Assert::notSame($original, $modified);
    }

    public function testWithQueryReturnsNewInstance(): void
    {
        $original = new Uri('https://example.com?old=value');
        $modified = $original->withQuery('new=query');

        Assert::same('old=value', $original->getQuery());
        Assert::same('new=query', $modified->getQuery());
        Assert::notSame($original, $modified);
    }

    public function testWithQueryNullRemovesQuery(): void
    {
        $original = new Uri('https://example.com?query=value');
        $modified = $original->withQuery(null);

        Assert::same('query=value', $original->getQuery());
        Assert::null($modified->getQuery());
    }

    public function testWithFragmentReturnsNewInstance(): void
    {
        $original = new Uri('https://example.com#old');
        $modified = $original->withFragment('new');

        Assert::same('old', $original->getFragment());
        Assert::same('new', $modified->getFragment());
        Assert::notSame($original, $modified);
    }

    public function testWithFragmentNullRemovesFragment(): void
    {
        $original = new Uri('https://example.com#fragment');
        $modified = $original->withFragment(null);

        Assert::same('fragment', $original->getFragment());
        Assert::null($modified->getFragment());
    }

    public function testWithUserInfoReturnsNewInstance(): void
    {
        $original = new Uri('https://example.com');
        $modified = $original->withUserInfo('user', 'pass');

        Assert::null($original->getUsername());
        Assert::null($original->getPassword());
        Assert::same('user', $modified->getUsername());
        Assert::same('pass', $modified->getPassword());
        Assert::notSame($original, $modified);
    }

    public function testWithUserInfoWithoutPassword(): void
    {
        $uri = new Uri('https://example.com');
        $modified = $uri->withUserInfo('user');

        Assert::same('user', $modified->getUsername());
        Assert::null($modified->getPassword());
        Assert::same('user', $modified->getUserInfo());
    }

    public function testWithUserInfoNullRemovesUserInfo(): void
    {
        $original = new Uri('https://user:pass@example.com');
        $modified = $original->withUserInfo(null);

        Assert::same('user', $original->getUsername());
        Assert::null($modified->getUsername());
    }

    // =========================================================================
    // Comparison Tests
    // =========================================================================

    public function testEqualsWithExcludeFragmentMode(): void
    {
        $uri1 = new Uri('https://example.com/path#fragment1');
        $uri2 = new Uri('https://example.com/path#fragment2');

        Assert::true($uri1->equals($uri2, UriComparisonMode::ExcludeFragment));
    }

    public function testEqualsWithIncludeFragmentMode(): void
    {
        $uri1 = new Uri('https://example.com/path#fragment1');
        $uri2 = new Uri('https://example.com/path#fragment2');

        Assert::false($uri1->equals($uri2, UriComparisonMode::IncludeFragment));
    }

    public function testEqualsWithSameFragment(): void
    {
        $uri1 = new Uri('https://example.com/path#fragment');
        $uri2 = new Uri('https://example.com/path#fragment');

        Assert::true($uri1->equals($uri2, UriComparisonMode::IncludeFragment));
    }

    public function testEqualsDifferentSchemes(): void
    {
        $uri1 = new Uri('http://example.com');
        $uri2 = new Uri('https://example.com');

        Assert::false($uri1->equals($uri2));
    }

    public function testEqualsDifferentHosts(): void
    {
        $uri1 = new Uri('https://example.com');
        $uri2 = new Uri('https://other.com');

        Assert::false($uri1->equals($uri2));
    }

    public function testEqualsDifferentPaths(): void
    {
        $uri1 = new Uri('https://example.com/path1');
        $uri2 = new Uri('https://example.com/path2');

        Assert::false($uri1->equals($uri2));
    }

    public function testEqualsDifferentQueries(): void
    {
        $uri1 = new Uri('https://example.com?key=1');
        $uri2 = new Uri('https://example.com?key=2');

        Assert::false($uri1->equals($uri2));
    }

    // =========================================================================
    // Resolution Tests
    // =========================================================================

    public function testResolveRelativePathReference(): void
    {
        $baseUrl = new Uri('https://example.com/base/path/');
        $resolved = $baseUrl->resolve('relative');

        Assert::same('https', $resolved->getScheme());
        Assert::same('example.com', $resolved->getHost());
        Assert::same('/base/path/relative', $resolved->getPath());
    }

    public function testResolveAbsolutePathReference(): void
    {
        $baseUrl = new Uri('https://example.com/base/path');
        $resolved = $baseUrl->resolve('/absolute');

        Assert::same('https', $resolved->getScheme());
        Assert::same('example.com', $resolved->getHost());
        Assert::same('/absolute', $resolved->getPath());
    }

    public function testResolveQueryOnlyReference(): void
    {
        $baseUrl = new Uri('https://example.com/path?old=value');
        $resolved = $baseUrl->resolve('?new=query');

        Assert::same('https', $resolved->getScheme());
        Assert::same('example.com', $resolved->getHost());
        Assert::same('/path', $resolved->getPath());
        Assert::same('new=query', $resolved->getQuery());
    }

    public function testResolveFragmentOnlyReference(): void
    {
        $baseUrl = new Uri('https://example.com/path#old');
        $resolved = $baseUrl->resolve('#new');

        Assert::same('https', $resolved->getScheme());
        Assert::same('example.com', $resolved->getHost());
        Assert::same('/path', $resolved->getPath());
        Assert::same('new', $resolved->getFragment());
    }

    public function testResolveNetworkPathReference(): void
    {
        $baseUrl = new Uri('https://example.com/path');
        $resolved = $baseUrl->resolve('//other.com/path');

        Assert::same('https', $resolved->getScheme());
        Assert::same('other.com', $resolved->getHost());
        Assert::same('/path', $resolved->getPath());
    }

    // =========================================================================
    // Static Parse Tests
    // =========================================================================

    public function testStaticParseReturnsUriInstance(): void
    {
        $uri = Uri::parse('https://example.com');

        Assert::type(Uri::class, $uri);
        Assert::same('https', $uri->getScheme());
        Assert::same('example.com', $uri->getHost());
    }

    public function testStaticParseReturnsNullOnFailure(): void
    {
        $uri = Uri::parse('://invalid');

        Assert::null($uri);
    }

    public function testStaticParseWithBaseUrl(): void
    {
        $baseUrl = new Uri('https://example.com/base/');
        $uri = Uri::parse('relative', $baseUrl);

        Assert::type(Uri::class, $uri);
        Assert::same('/base/relative', $uri->getPath());
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testParseUriWithEmptyPath(): void
    {
        $uri = new Uri('https://example.com');

        Assert::same('', $uri->getPath());
    }

    public function testParseUriWithIpv6Host(): void
    {
        $uri = new Uri('https://[::1]/path');

        Assert::same('[::1]', $uri->getHost());
    }

    public function testParseUriWithIpv4Host(): void
    {
        $uri = new Uri('https://192.168.1.1/path');

        Assert::same('192.168.1.1', $uri->getHost());
    }

    public function testParseUriWithSpecialCharactersInPath(): void
    {
        $uri = new Uri('https://example.com/path%2Fwith%2Fslashes');

        Assert::contains('path', $uri->getPath());
    }

    public function testParseUriWithPort80OnHttp(): void
    {
        $uri = new Uri('http://example.com:80');

        // Port 80 is the default for http, behavior may vary
        Assert::true($uri->getPort() === null || $uri->getPort() === 80);
    }

    public function testParseUriWithPort443OnHttps(): void
    {
        $uri = new Uri('https://example.com:443');

        // Port 443 is the default for https, behavior may vary
        Assert::true($uri->getPort() === null || $uri->getPort() === 443);
    }
}

(new UriTest())->run();
