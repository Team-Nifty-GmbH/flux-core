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
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\Tags\TagsServiceProvider;
use Spatie\Translatable\TranslatableServiceProvider;
use Spatie\TranslationLoader\TranslationServiceProvider;
use TeamNiftyGmbH\Calendar\CalendarServiceProvider;
use TeamNiftyGmbH\DataTable\DataTableServiceProvider;
use WireUi\Heroicons\HeroiconsServiceProvider;
use WireUi\Providers\WireUiServiceProvider;

use function Orchestra\Testbench\package_path;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use CreatesApplication {
        CreatesApplication::createApplication as createBaseApplication;
    }

    protected $loadEnvironmentVariables = true;

    public function createApplication(): Application
    {
        $app = $this->createBaseApplication();

        // copy the schema from the package to the testbench skeleton
        if (! file_exists(database_path('migrations/schema'))) {
            mkdir(database_path('migrations/schema'), recursive: true);
        }
        if (! file_exists(database_path('migrations/schema/mysql-schema.sql'))) {
            copy(
                __DIR__ . '/../database/schema/mysql-schema.sql',
                database_path('migrations/schema/mysql-schema.sql')
            );
        }

        return $app;
    }

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
            ViewServiceProvider::class,
            PermissionServiceProvider::class,
            TagsServiceProvider::class,
            ScoutServiceProvider::class,
            HeroiconsServiceProvider::class,
            WireUiServiceProvider::class,
            MediaLibraryServiceProvider::class,
            CalendarServiceProvider::class,
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
}
