<?php

namespace FluxErp\Providers;

use FluxErp\Support\Broadcasting\CombinedBroadcaster;
use FluxErp\Support\Broadcasting\FluxMercureBroadcaster;
use FluxErp\Support\Broadcasting\MercureHubConfig;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        resolve_static(MercureHubConfig::class, 'apply');

        Broadcast::routes();

        require __DIR__ . '/../../routes/channels.php';
    }

    public function register(): void
    {
        $this->callAfterResolving(
            BroadcastManager::class,
            fn (BroadcastManager $manager) => $manager->extend(
                'mercure',
                function (Application $app, array $config) use ($manager): FluxMercureBroadcaster {
                    $config['client_events'] ??= false;

                    return resolve_static(
                        FluxMercureBroadcaster::class,
                        'fromBroadcaster',
                        [(fn () => $this->createMercureDriver($config))->call($manager)]
                    );
                }
            )
        );

        $this->callAfterResolving(
            BroadcastManager::class,
            fn (BroadcastManager $manager) => $manager->extend(
                'combined',
                fn (Application $app, array $config): CombinedBroadcaster => app(
                    CombinedBroadcaster::class,
                    [
                        'broadcasters' => array_map(
                            fn (string $connection): Broadcaster => $app->make(BroadcastManager::class)
                                ->connection($connection),
                            $config['connections']
                        ),
                    ]
                )
            )
        );
    }
}
