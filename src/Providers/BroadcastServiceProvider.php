<?php

namespace FluxErp\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public const string QUEUE_CONNECTION = 'flux-broadcast';

    public function boot(): void
    {
        Broadcast::routes();

        require __DIR__ . '/../../routes/channels.php';
    }

    public function register(): void
    {
        if (is_null(Config::get('queue.connections.' . static::QUEUE_CONNECTION))) {
            Config::set('queue.connections.' . static::QUEUE_CONNECTION, ['driver' => 'deferred']);
        }
    }
}
