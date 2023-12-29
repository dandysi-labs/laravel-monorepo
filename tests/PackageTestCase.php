<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests;

use Dandysi\Laravel\Monorepo\MonorepoProvider;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MonorepoProvider::class,
        ];
    }
}