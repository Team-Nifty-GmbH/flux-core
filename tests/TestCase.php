<?php

namespace FluxErp\Tests;

use App\Providers\AppServiceProvider;
use Dotenv\Dotenv;
use FluxErp\FluxServiceProvider;
use FluxErp\Providers\FortifyServiceProvider;
use FluxErp\Providers\RouteServiceProvider;
use FluxErp\Providers\SanctumServiceProvider;
use Hammerstone\FastPaginate\FastPaginateProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use TeamNiftyGmbH\Calendar\CalendarServiceProvider;
use TeamNiftyGmbH\DataTable\DataTableServiceProvider;
use WireUi\Heroicons\HeroiconsServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        if (file_exists(__DIR__ . '/../../../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
            $dotenv->load();
        }

        parent::setUp();

        config([
            'auth.defaults.guard' => 'sanctum',
        ]);
    }

    public function getPackageProviders($app)
    {
        return [
            PermissionServiceProvider::class,
            ScoutServiceProvider::class,
            HeroiconsServiceProvider::class,
            MediaLibraryServiceProvider::class,
            CalendarServiceProvider::class,
            LivewireServiceProvider::class,
            FastPaginateProvider::class,
            QueryBuilderServiceProvider::class,
            FortifyServiceProvider::class,
            DataTableServiceProvider::class,
            ActivitylogServiceProvider::class,
            MediaLibraryServiceProvider::class,
            FluxServiceProvider::class,
            RouteServiceProvider::class,
            SanctumServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        if (file_exists(base_path('../../../../../../.env'))) {
            $dotenv = Dotenv::createImmutable(base_path('../../../../../../'));
            $dotenv->load();
        }
    }
}
