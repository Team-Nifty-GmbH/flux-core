<?php

namespace FluxErp\Tests;

use Barryvdh\DomPDF\ServiceProvider;
use FluxErp\FluxServiceProvider;
use FluxErp\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\Tags\TagsServiceProvider;
use Spatie\Translatable\TranslatableServiceProvider;
use Spatie\TranslationLoader\TranslationServiceProvider;
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
            TranslationServiceProvider::class,
            TranslatableServiceProvider::class,
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
            FluxServiceProvider::class,
            WebPushServiceProvider::class,
            ServiceProvider::class,
            ExcelServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        if (! is_dir(database_path('settings'))) {
            mkdir(database_path('settings'));
        }

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.collation', 'utf8mb4_unicode_ci');
        $app['config']->set('flux.install_done', true);
        $app['config']->set('auth.defaults.guard', 'sanctum');
        $app['config']->set('auth.providers.users.model', User::class);
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
