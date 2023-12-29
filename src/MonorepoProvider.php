<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo;

use BadMethodCallException;
use Dandysi\Laravel\Monorepo\Console\ProviderGeneratorCommand;
use Dandysi\Laravel\Monorepo\Console\TestCaseGeneratorCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MonorepoProvider extends ServiceProvider
{
    // the env variable to retrieve provider from
    public const PROVIDER_ENV = 'MONOREPO_PROVIDER';
    // the conf key to retrieve provider from
    public const PROVIDER_CONF = 'monorepo.provider';


    public function register(): void
    {
        $definedProvider = static::definedProvider();

        if (!$definedProvider) {
            return;
        }

        if ($definedProvider !== static::class) {
            $this->app->register($definedProvider);
            return;
        }

        $this->booted(function () {
            if (!method_exists($this->app, 'routesAreCached') or !$this->app->routesAreCached()) {
                $this->registerRoutes();
            }
        });

        $this->booting(function () {
            $eventListeners = $this->registerEventListeners();
            foreach ($eventListeners as $event => $listeners) {
                foreach (array_unique($listeners) as $listener) {
                    Event::listen($event, $listener);
                }
            }

            $eventSubscribers = $this->registerEventSubscribers();
            foreach($eventSubscribers as $eventSubscriber) {
                Event::subscribe($eventSubscriber);
            }
        });

        if (method_exists($this->app, 'configurationIsCached') and $this->app->configurationIsCached()) {
            return;
        }

        //set in case next time we run env isn't available
        Config::set(self::PROVIDER_CONF, $definedProvider);

        foreach ($this->registerConfig() as $key => $values) {
            Config::set($key, $values);
        }
    }

    /**
     * Determine if a provider has been defined.
     * @return string|null
     */
    public static function definedProvider(): string|null
    {
        // can we use config yet (we might be running from inside a config file)
        if (!Config::getFacadeRoot() or !Config::has(self::PROVIDER_CONF)) {
            return env(self::PROVIDER_ENV, null);
        }

        return Config::get(self::PROVIDER_CONF);
    }

    public function boot(): void
    {
        $commands = [];

        if (static::definedProvider()) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                $this->registerSchedule($schedule);
            });

            $commands = $this->registerCommands();
        }

        $this->commands(array_merge(
            [
                ProviderGeneratorCommand::class,
                TestCaseGeneratorCommand::class
            ],
            $commands
        ));
    }

    /**
     * Registered any routes
     * @return void
     */
    protected function registerRoutes(): void
    {

    }

    /**
     * Register any commands.
     * Array values will be command class names
     * @return array
     */
    protected function registerCommands(): array
    {
        return [];
    }

    /**
     * Register any event listeners.
     * Array keys will be events and values related listeners
     * @return array
     */
    protected function registerEventListeners(): array
    {
        return [];
    }

    /**
     * Register any event subscribers
     * Array values will be subscriber class names
     * @return array
     */
    protected function registerEventSubscribers(): array
    {
        return [];
    }

    /**
     * Register any schedules
     * @param Schedule $schedule
     * @return void
     */
    protected function registerSchedule(Schedule $schedule): void
    {

    }

    /**
     * Register any config
     * Array key and values relate to config counterparts
     * @return array
     */
    protected function registerConfig(): array
    {
        return [];
    }

    /**
     * Magic function to allow config files to be adapted via defined provider
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (!str_ends_with($name, 'Config')) {
            throw new BadMethodCallException($name);
        }

        $default = reset($arguments);
        $definedProvider = static::definedProvider();

        if (!$definedProvider) {
            return $default;
        }

        if (method_exists($definedProvider, $name)) {
            return $definedProvider::$name($default);
        }

        return $default;
    }
}
