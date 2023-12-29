<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests\Fixtures;

use Dandysi\Laravel\Monorepo\MonorepoProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;

class DummyMonorepoProvider extends MonorepoProvider
{
    public static function favouriteColourConfig(string $default): string
    {
        return 'blue';
    }

    public static function likedColoursConfig(array $default): array
    {
        $default['blue'] = true;
        $default['red'] = true;
        return $default;
    }

    protected function registerRoutes(): void
    {
        Route::get('/test1', function() { return 'It worked 1!';});
        Route::get('/test2', function() { return 'It worked 2!';});
    }

    protected function registerCommands(): array
    {
        return [
            DummyCommand::class
        ];
    }

    protected function registerSchedule(Schedule $schedule): void
    {
        $schedule->command(DummyCommand::class)->mondays();
    }

    protected function registerEventListeners(): array
    {
        return [
            DummyEvent::class => [
                DummyEventListener::class,
                DummyEventListener::class, //duplicate intentional
            ]
        ];
    }

    public function registerEventSubscribers(): array
    {
        return [
            DummyEventSubscriber::class
        ];
    }

    protected function registerConfig(): array
    {
        return ['dummy' => ['item1' => true, 'item2' => false]];
    }
}
