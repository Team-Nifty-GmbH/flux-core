<?php

namespace FluxErp\Listeners;

use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CacheKeyWrittenListener
{
    /**
     * Handle the event.
     *
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(KeyWritten $event): void
    {
        $keyIndexName = Str::slug(config('app.name')) . ':cache-keys';
        if ($event->key === $keyIndexName || ! Str::startsWith($event->key, config('app.name'))) {
            return;
        }

        $cacheKeys = cache()->get($keyIndexName) ?? [];
        $cacheKeys[$event->key] = ['tags' => $event->tags];

        cache()->forever($keyIndexName, $cacheKeys);
    }
}
