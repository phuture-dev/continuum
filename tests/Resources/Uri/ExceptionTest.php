<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests\Resources\Uri;

use Tester\Assert;
use Tester\TestCase;
use Uri\{InvalidUriException, UriException};
use Uri\WhatWg\{InvalidUrlException, Url, UrlValidationError, UrlValidationErrorType};

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class ExceptionTest extends TestCase
{
    public function testUriExceptionIsException(): void
    {
        $exception = new UriException('Test message');

        Assert::type(\Exception::class, $exception);
        Assert::same('Test message', $exception->getMessage());
    }

    public function testUriExceptionWithCode(): void
    {
        $exception = new UriException('Test message', 100);

        Assert::same(100, $exception->getCode());
    }

    public function testUriExceptionWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous error');
        $exception = new UriException('Test message', 0, $previous);

        Assert::same($previous, $exception->getPrevious());
    }

    public function testInvalidUriExceptionExtendsUriException(): void
    {
        $exception = new InvalidUriException('Invalid URI');

        Assert::type(UriException::class, $exception);
        Assert::type(\Exception::class, $exception);
    }

    public function testInvalidUriExceptionCanBeThrown(): void
    {
        Assert::exception(
            fn() => throw new InvalidUriException('Test exception'),
            InvalidUriException::class,
            'The specified URI is malformed; Test exception'
        );
    }

    public function testInvalidUriExceptionCanBeCaught(): void
    {
        $caught = false;
        try {
            throw new InvalidUriException('Test exception');
        } catch (InvalidUriException $e) {
            $caught = true;
            Assert::same('The specified URI is malformed; Test exception', $e->getMessage());
        }
        Assert::true($caught);
    }

    public function testInvalidUriExceptionCanBeCaughtAsUriException(): void
    {
        $caught = false;
        try {
            throw new InvalidUriException('Test exception');
        } catch (UriException $e) {
            $caught = true;
            Assert::type(InvalidUriException::class, $e);
        }
        Assert::true($caught);
    }

    public function testInvalidUriExceptionWithCodeAndPrevious(): void
    {
        $previous = new \RuntimeException('Previous');
        $exception = new InvalidUriException('Invalid URI', 500, $previous);

        Assert::same('The specified URI is malformed; Invalid URI', $exception->getMessage());
        Assert::same(500, $exception->getCode());
        Assert::same($previous, $exception->getPrevious());
    }

    public function testInvalidUrlExceptionExtendsInvalidUriException(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );
        $exception = new InvalidUrlException('Invalid URL', [$error]);

        Assert::type(InvalidUriException::class, $exception);
        Assert::type(UriException::class, $exception);
    }

    public function testInvalidUrlExceptionHasErrorsProperty(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );
        $exception = new InvalidUrlException('Invalid URL', [$error]);

        Assert::type('array', $exception->errors);
        Assert::count(1, $exception->errors);
        Assert::same($error, $exception->errors[0]);
    }

    public function testInvalidUrlExceptionCanBeThrown(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );

        Assert::exception(
            fn() => throw new InvalidUrlException('Test exception', [$error]),
            InvalidUrlException::class,
            'The specified URI is malformed; Test exception (PortInvalid)'
        );
    }

    public function testInvalidUrlExceptionCanBeCaught(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );
        $caught = false;
        try {
            throw new InvalidUrlException('Test exception', [$error]);
        } catch (InvalidUrlException $e) {
            $caught = true;
            Assert::same('The specified URI is malformed; Test exception (PortInvalid)', $e->getMessage());
        }
        Assert::true($caught);
    }

    public function testInvalidUrlExceptionCanBeCaughtAsInvalidUriException(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );
        $caught = false;
        try {
            throw new InvalidUrlException('Test exception', [$error]);
        } catch (InvalidUriException $e) {
            $caught = true;
            Assert::type(InvalidUrlException::class, $e);
        }
        Assert::true($caught);
    }

    public function testInvalidUrlExceptionCanBeCaughtAsUriException(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );
        $caught = false;
        try {
            throw new InvalidUrlException('Test exception', [$error]);
        } catch (UriException $e) {
            $caught = true;
            Assert::type(InvalidUrlException::class, $e);
        }
        Assert::true($caught);
    }

    public function testInvalidUrlExceptionFromInvalidUrl(): void
    {
        try {
            new Url('not-a-valid-url-without-base');
            Assert::fail('Expected InvalidUrlException was not thrown');
        } catch (InvalidUrlException $e) {
            Assert::type('array', $e->errors);
            Assert::true(count($e->errors) > 0);
        }
    }

    public function testInvalidUrlExceptionWithCodeAndPrevious(): void
    {
        $previous = new \RuntimeException('Previous');
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );
        $exception = new InvalidUrlException('Invalid URL', [$error], 400, $previous);

        Assert::same('The specified URI is malformed; Invalid URL (PortInvalid)', $exception->getMessage());
        Assert::same(400, $exception->getCode());
        Assert::same($previous, $exception->getPrevious());
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

    public function testUrlValidationErrorHasTypeProperty(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );

        Assert::same(UrlValidationErrorType::PortInvalid, $error->type);
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

    public function testUrlValidationErrorWithFailureFalse(): void
    {
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::SpecialSchemeMissingFollowingSolidus,
            false
        );

        Assert::false($error->failure);
    }

    public function testUrlValidationErrorTypeDomainInvalidCodePoint(): void
    {
        Assert::same(UrlValidationErrorType::DomainInvalidCodePoint, UrlValidationErrorType::DomainInvalidCodePoint);
    }

    public function testUrlValidationErrorTypeHostInvalidCodePoint(): void
    {
        Assert::same(UrlValidationErrorType::HostInvalidCodePoint, UrlValidationErrorType::HostInvalidCodePoint);
    }

    public function testUrlValidationErrorTypeHostMissing(): void
    {
        Assert::same(UrlValidationErrorType::HostMissing, UrlValidationErrorType::HostMissing);
    }

    public function testUrlValidationErrorTypeInvalidUrlUnit(): void
    {
        Assert::same(UrlValidationErrorType::InvalidUrlUnit, UrlValidationErrorType::InvalidUrlUnit);
    }

    public function testUrlValidationErrorTypePortInvalid(): void
    {
        Assert::same(UrlValidationErrorType::PortInvalid, UrlValidationErrorType::PortInvalid);
    }

    public function testUrlValidationErrorTypePortOutOfRange(): void
    {
        Assert::same(UrlValidationErrorType::PortOutOfRange, UrlValidationErrorType::PortOutOfRange);
    }

    public function testUrlValidationErrorTypeMissingSchemeNonRelativeUrl(): void
    {
        Assert::same(UrlValidationErrorType::MissingSchemeNonRelativeUrl, UrlValidationErrorType::MissingSchemeNonRelativeUrl);
    }

    public function testUrlValidationErrorTypeSpecialSchemeMissingFollowingSolidus(): void
    {
        Assert::same(UrlValidationErrorType::SpecialSchemeMissingFollowingSolidus, UrlValidationErrorType::SpecialSchemeMissingFollowingSolidus);
    }

    public function testExceptionHierarchy(): void
    {
        // Test that the exception hierarchy is correct
        $error = new UrlValidationError(
            'context',
            UrlValidationErrorType::PortInvalid,
            true
        );
        $invalidUrlException = new InvalidUrlException('test', [$error]);
        $invalidUriException = new InvalidUriException('test');
        $uriException = new UriException('test');

        Assert::type(UriException::class, $invalidUriException);
        Assert::type(UriException::class, $invalidUrlException);
        Assert::type(InvalidUriException::class, $invalidUrlException);
        Assert::type(\Exception::class, $uriException);
    }
}

(new ExceptionTest())->run();
