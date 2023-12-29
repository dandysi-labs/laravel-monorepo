<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests\Fixtures;

use Illuminate\Console\Command;

class DummyCommand extends Command
{
    protected $signature = 'dummy_one:test';

    public function handle(): void
    {

    }
}
