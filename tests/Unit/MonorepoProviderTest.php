<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests\Unit;

use Dandysi\Laravel\Monorepo\MonorepoProvider;
use Dandysi\Laravel\Monorepo\Tests\Fixtures\DummyMonorepoProvider;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Application;

class MonorepoProviderTest extends TestCase
{
    public function setUp(): void
    {
        putenv(MonorepoProvider::PROVIDER_ENV);
        //we want to test when Config facade not resolved
        Config::setFacadeApplication(null);
        Config::clearResolvedInstances();
    }

    /** @test */
    public function it_returns_null_provider()
    {
        $this->assertNull(MonorepoProvider::definedProvider());
    }

    /** @test */
    public function it_returns_defined_provider_when_app_defined_but_no_config_resolved()
    {
        putenv(MonorepoProvider::PROVIDER_ENV.'='.DummyMonorepoProvider::class);
        $app = new Application();
        Config::setFacadeApplication($app);
        $this->assertSame(DummyMonorepoProvider::class, MonorepoProvider::definedProvider());
    }

    /** @test */
    public function it_returns_defined_provider_when_config_resolved_and_set()
    {
        putenv(MonorepoProvider::PROVIDER_ENV.'=SomeOtherClass');
        Config::shouldReceive('has')->with(MonorepoProvider::PROVIDER_CONF)->andReturn(true);
        Config::shouldReceive('get')->with(MonorepoProvider::PROVIDER_CONF)->andReturn(DummyMonorepoProvider::class);
        $this->assertSame(DummyMonorepoProvider::class, MonorepoProvider::definedProvider());
    }

    /** @test */
    public function it_returns_defined_provider_when_config_resolved_and_not_set()
    {
        putenv(MonorepoProvider::PROVIDER_ENV.'='.DummyMonorepoProvider::class);
        Config::shouldReceive('has')->with(MonorepoProvider::PROVIDER_CONF)->andReturn(false);
        $this->assertSame(DummyMonorepoProvider::class, MonorepoProvider::definedProvider());
    }

    /** @test */
    public function it_returns_default_config_values_when_no_defined_provider()
    {
        $default = 'Some value';
        // @phpstan-ignore method.undefined
        $this->assertSame($default, MonorepoProvider::nonExistentConfig($default));
    }    

    /** @test */
    public function it_fails_when_non_config_method_does_not_exist()
    {
        $this->expectException(\BadMethodCallException::class);
        // @phpstan-ignore method.undefined
        MonorepoProvider::nonExistent();
    }
}
