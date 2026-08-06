<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use SortDirection;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for PHP 8.6 polyfill methods.
 *
 * @testCase
 */
class Php86Test extends TestCase
{
    // =========================================================================
    // SortDirection Tests
    // =========================================================================

    public function testSortDirectionExists(): void
    {
        Assert::true(class_exists(SortDirection::class));
    }

    public function testSortDirectionStubClass(): void
    {
        if (PHP_VERSION_ID >= 80100) {
            \Tester\Environment::skip('The stub class is only used on PHP < 8.1.');
        }

        Assert::false(enum_exists(SortDirection::class));
        Assert::same('ASC', SortDirection::Ascending);
        Assert::same('DESC', SortDirection::Descending);
    }

    public function testSortDirectionEnum(): void
    {
        if (PHP_VERSION_ID < 80100) {
            \Tester\Environment::skip('Enums are only supported on PHP >= 8.1.');
        }

        Assert::true(enum_exists(SortDirection::class));
        Assert::true(SortDirection::Ascending instanceof SortDirection);
        Assert::true(SortDirection::Descending instanceof SortDirection);

        $cases = SortDirection::cases();
        Assert::count(2, $cases);
        Assert::same('Ascending', $cases[0]->name);
        Assert::same('Descending', $cases[1]->name);
    }
}

(new Php86Test())->run();
