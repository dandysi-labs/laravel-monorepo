<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests\Feature;

use Dandysi\Laravel\Monorepo\MonorepoProvider;
use Dandysi\Laravel\Monorepo\Tests\Fixtures\DummyCommand;
use Dandysi\Laravel\Monorepo\Tests\Fixtures\DummyEvent;
use Dandysi\Laravel\Monorepo\Tests\Fixtures\DummyMonorepoProvider;
use Dandysi\Laravel\Monorepo\Tests\PackageTestCase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;

class MonorepoProviderTest extends PackageTestCase
{
    private string $cacheFile;
    public function tearDown(): void
    {
        if (isset($this->cacheFile)) {
            unlink($this->cacheFile);
        }
        parent::tearDown();
    }

    public function setUp(): void
    {
        putenv(MonorepoProvider::PROVIDER_ENV.'='.DummyMonorepoProvider::class);
        parent::setUp();
    }

    /** @test */
    public function it_works_when_no_defined_provider()
    {
        putenv(MonorepoProvider::PROVIDER_ENV);
        $this->refreshApplication();
        $this->assertNull(MonorepoProvider::definedProvider());
        $this->assertRouteCount(0);
        $this->assertEventListenerCount(DummyEvent::class, 0);
        $this->assertScheduleCount(0);
        $this->assertCommandNotRegistered(DummyCommand::class);
        $this->assertConfigNotRegistered('dummy');

        $default = [
            'blue' => false,
            'red' => false,
            'green' => false,
        ];
        $this->assertSame($default, MonorepoProvider::likedColoursConfig($default));
    }

    /** @test */
    public function it_returns_default_value_when_config_function_does_not_exist()
    {
        $default = 'Some String';
        $this->assertSame($default, MonorepoProvider::nonExistentConfig($default));
    }

    /** @test */
    public function it_returns_correct_value_when_config_function_exists()
    {
        $this->assertSame('blue', MonorepoProvider::favouriteColourConfig('unknown'));
    }

    /** @test */
    public function it_returns_modified_value_when_config_function_exists()
    {
        $default = [
            'blue' => false,
            'red' => false,
            'green' => false,
        ];

        $expected = $default;
        $expected['blue'] = true;
        $expected['red'] = true;

        $this->assertSame($expected, MonorepoProvider::likedColoursConfig($default));
    }


    /** @test */
    public function it_registers_routes()
    {
        $this->assertRouteCount(2);
    }

    /** @test */
    public function it_does_not_register_routes_if_already_cached()
    {
        $this->cacheFile = $this->createCacheFile($this->app->getCachedRoutesPath(), []);
        $this->refreshApplication();
        //no routes in cache file
        $this->assertRouteCount(0);
    }

    /** @test */
    public function it_registers_commands()
    {
        $this->assertCommandRegistered('dummy_one:test');
    }

    /** @test */
    public function it_registers_schedules()
    {
        $this->assertScheduleCount(1);
    }

    /** @test */
    public function it_registers_config()
    {
        $this->assertNotNull(Config::get('dummy'));
    }

    /** @test */
    public function it_does_not_register_config_if_already_cached()
    {
        $data = Config::all();
        unset($data['dummy']);
        $this->cacheFile = $this->createCacheFile($this->app->getCachedConfigPath(), $data);
        $this->refreshApplication();
        //config not registered
        $this->assertConfigNotRegistered('dummy');
    }

    /** @test */
    public function it_registers_event_listeners()
    {
        //one registered via event listener and one via subscriber
        $this->assertEventListenerCount(DummyEvent::class, 2);
    }

    private function createCacheFile(string $path, $data): string
    {
        file_put_contents($path, '<?php return ' . var_export($data, true) . ';');
        return $path;
    }


    protected function assertCommandRegistered(string $command): void
    {
        $commands = app(Kernel::class)->all();
        $this->assertArrayHasKey($command, $commands);
    }

    protected function assertConfigNotRegistered(string $key): void
    {
        $this->assertFalse(Config::has($key));
    }

    protected function assertCommandNotRegistered(string $command): void
    {
        $commands = app(Kernel::class)->all();
        $this->assertArrayNotHasKey($command, $commands);
    }

    protected function assertEventListenerCount(string $event, int $expectedCount): void
    {
        $this->assertCount(
            $expectedCount,
            app(Dispatcher::class)->getListeners($event),
            'Registered Event Listeners'
        );
    }

    protected function assertScheduleCount(int $expectedCount): void
    {
        $this->assertCount(
            $expectedCount,
            app(Schedule::class)->events(),
            'Registered Schedules'
        );
    }

    protected function assertRouteCount(int $expectedCount): void
    {
        $this->assertCount(
            $expectedCount,
            app(Router::class)->getRoutes(),
            'Registered Routes'
        );
    }
}