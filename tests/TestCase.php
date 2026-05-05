<?php

namespace FluxErp\Tests;

use Barryvdh\DomPDF\ServiceProvider;
use FluxErp\FluxServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Illuminate\Support\Facades\File;
use Laragear\TwoFactor\TwoFactorServiceProvider;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelPasskeys\LaravelPasskeysServiceProvider;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\Tags\TagsServiceProvider;
use TallStackUi\Facades\TallStackUi;
use TallStackUi\TallStackUiServiceProvider;
use TeamNiftyGmbH\DataTable\DataTableServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, WithCachedConfig, WithCachedRoutes;

    protected $loadEnvironmentVariables = true;

    public function getPackageProviders($app): array
    {
        return [
            LaravelSettingsServiceProvider::class,
            LivewireServiceProvider::class,
            TallStackUiServiceProvider::class,
            PermissionServiceProvider::class,
            TagsServiceProvider::class,
            ScoutServiceProvider::class,
            MediaLibraryServiceProvider::class,
            QueryBuilderServiceProvider::class,
            DataTableServiceProvider::class,
            ActivitylogServiceProvider::class,
            MediaLibraryServiceProvider::class,
            LaravelPasskeysServiceProvider::class,
            TwoFactorServiceProvider::class,
            FluxServiceProvider::class,
            WebPushServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        File::makeDirectory(path: database_path('settings'), force: true);

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.collation', 'utf8mb4_unicode_ci');
        $app['config']->set('flux.install_done', true);
        $app['config']->set('auth.defaults.guard', 'sanctum');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('settings.auto_discover_settings', []);
    }

    protected function getPackageAliases($app): array
    {
        return [
            'TallStackUi' => TallStackUi::class,
        ];
    }
}
