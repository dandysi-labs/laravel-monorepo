
# Laravel Monorepo

[![Actions Status](https://github.com/dandysi-labs/laravel-monorepo/workflows/Tests/badge.svg)](https://github.com/dandysi-labs/laravel-monorepo/actions)

This package allows you to create a monorepo of related Laravel microservices, that share common libraries and backend services/technologies. 

## Demo

A demo repository using this package containing three related Laravel microservices.

https://github.com/dandysi-labs/demo-laravel-monorepo

## Install

```bash
composer require dandysi/laravel-monorepo
```

## Create Provider

If you intend to use the normal Laravel directory structure:

```bash
php artisan make:monorepo-provider Chores
```
 
> This will create the file **app/Chores/MonorepoProvider.php** with the namespace **App\Chores**

Outside of the normal then you will need to specify the destination directory (this will be relative to the project root):

```bash
php artisan make:monorepo-provider Chores microservices/Chores
```

> This will create the file **microservices/Chores/MonorepoProvider..php** with the namespace **Chores**

...and update your composer.json autoload paths accordingly:

```json
"autoload": {
    "psr-4": {
        "Chores\\": "microservices/Chores"
    }
}
```

## Next Step

Start writing your code as you normally would, albeit using the monorepo provider to setup the service.

### Routes

Add routes as usual (this function will not be called if routes are already cached).

```php

use Illuminate\Support\Facades\Route;

protected function configureRoutes()
{
    Route::middleware('api')
        ->prefix('api')
        ->group(__DIR__ . '/routes.php')
    ;
}
```

### Commands

Register command classes.

```php
use Chores\Console\ExpireArticlesCommand;

protected function registerCommands(): array
{
    return [
        ExpireArticlesCommand::class
    ];
}
```

### Schedules

```php
use Illuminate\Console\Scheduling\Schedule;

protected function registerSchedule(Schedule $schedule): void
{
    $schedule->command(ExpireArticlesCommand::class)->daily();
}
```

### Events

Register event listeners:

```php
protected function registerEventListeners(): array
{
    return [
        ArticleCreated::class => [
            UpdateArticleCachListener::class
        ],    
        ArticleDeleted::class => [
            UpdateArticleCachListener::class
        ]
    ];
}
```

Register event subscribers:

```php
protected function registerEventSubscribers(): array
{
    return [
        UpdateArticleCacheSubscriber::class
    ];
}
```

### Config

To include isolated config data (this function will not be called if config already cached):

```php
protected function registerConfig(): array
{
    return [
        'chores' => require __DIR__ . '/config.php'
    ];
}
```

If however you would like to tweak a normal config file further than just updating an environment variable, wrap the existing value(s) in a function call (this must be the base class and the method must end in **Config**)

```php
// config/database.php
use Dandysi\Laravel\Monorepo\MonorepoProvider;

return [
    'connections' => MonorepoProvider::dbConnectionsConfig([
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ]
    ])
];

```

...and create a function to perform the modification in the monorepo provider:

```php
// microservices/Chores/MonorepoPrvider.php

public static function dbConnectionsConfig(array $default): array
{
    $default['new_db'] => [
        // ...
    ];

    return $default;
}
```

## Testing

As routes, configs, commands, schedules, events are all isolated, testing is slightly more complicated. However, there is another make command to help. This will generate a test case for you to extend in your tests, effectively setting up the related microservice.

```bash
php artisan make:monorepo-test-case Chores/Tests microservices/Chores/Tests Chores
```

> This will create the file **microservices/Chores/Tests/TestCase.php** with the namespace **Chores\Tests** and use the **Chores\MonorepoProvider** class

## Using

To use your new microservice, simply update the environment variable MONOREPO_PROVIDER to point to the relevant monorepo provider class:

```
MONOREPO_PROVIDER=Chores\MonorepoProvider
```

To quickly run a command, prefix the command with the env

```bash
MONOREPO_PROVIDER=Chores\MonorepoProvider php artisan chores:some_command
```

Or serve some routes

```bash
MONOREPO_PROVIDER=Chores\MonorepoProvider php artisan serve
```

## Additional Providers

Simply create a new provider and repeat.

## License

Open-sourced software licensed under the [MIT license](LICENSE.md).
