<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests\Fixtures;

use Illuminate\Events\Dispatcher;

class DummyEventSubscriber
{
    public function handleEvent(DummyEvent $event): void
    {

    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            DummyEvent::class,
            [DummyEventSubscriber::class, 'handleEvent']
        );
    }
}
