<?php

namespace FluxErp\Tests;

use Barryvdh\DomPDF\ServiceProvider;
use Dotenv\Dotenv;
use FluxErp\FluxServiceProvider;
use FluxErp\Providers\BindingServiceProvider;
use FluxErp\Providers\EventServiceProvider;
use FluxErp\Providers\MorphMapServiceProvider;
use FluxErp\Providers\SanctumServiceProvider;
use FluxErp\Providers\ViewServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Spatie\Activitylog\ActivitylogServiceProvider;
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
    use CreatesApplication, RefreshDatabase;

    protected $loadEnvironmentVariables = true;

    protected function setUp(): void
    {
        if (file_exists(__DIR__ . '/../../../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
            $dotenv->load();
        }

        parent::setUp();
    }

    public function getPackageProviders($app): array
    {
        return [
            TranslationServiceProvider::class,
            TranslatableServiceProvider::class,
            LivewireServiceProvider::class,
            TallStackUiServiceProvider::class,
            ViewServiceProvider::class,
            PermissionServiceProvider::class,
            TagsServiceProvider::class,
            ScoutServiceProvider::class,
            MediaLibraryServiceProvider::class,
            QueryBuilderServiceProvider::class,
            DataTableServiceProvider::class,
            ActivitylogServiceProvider::class,
            MediaLibraryServiceProvider::class,
            FluxServiceProvider::class,
            BindingServiceProvider::class,
            SanctumServiceProvider::class,
            WebPushServiceProvider::class,
            MorphMapServiceProvider::class,
            EventServiceProvider::class,
            ServiceProvider::class,
            ExcelServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.collation', 'utf8mb4_unicode_ci');
        $app['config']->set('flux.install_done', true);
        $app['config']->set('auth.defaults.guard', 'sanctum');
        $app['config']->set('cache.default', 'array');
    }

    protected function getPackageAliases($app): array
    {
        return [
            'TallStackUi' => TallStackUi::class,
        ];
    }
}
