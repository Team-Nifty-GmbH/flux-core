<?php

namespace FluxErp\Listeners;

class BroadcastEventSubscriber
{
    /**
     * Handle incoming events.
     */
    public function broadcastEvent($event): void
    {
        $classReflection = new \ReflectionClass(get_class($event));
        ('FluxErp\\Events\\Broadcast' . $classReflection->getShortName())::dispatch($event->model);
    }

    /**
     * Register the listeners for the subscriber.
     * E.g. CommentCreated::class => 'broadcastEvent'
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events): array
    {
        return [

        ];
    }
}
