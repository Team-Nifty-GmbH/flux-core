<?php

namespace FluxErp\Tests;

use Dotenv\Dotenv;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use FluxErp\FluxServiceProvider;
use FluxErp\Providers\FortifyServiceProvider;
use FluxErp\Providers\RouteServiceProvider;
use FluxErp\Providers\SanctumServiceProvider;
use Hammerstone\FastPaginate\FastPaginateProvider;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Dusk\TestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\Tags\TagsServiceProvider;
use TeamNiftyGmbH\Calendar\CalendarServiceProvider;
use TeamNiftyGmbH\DataTable\DataTableServiceProvider;
use WireUi\Heroicons\HeroiconsServiceProvider;
use WireUi\Providers\WireUiServiceProvider;

abstract class DuskTestCase extends TestCase
{
    protected function setUp(): void
    {
        if (file_exists(__DIR__ . '/../../../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
            $dotenv->load();
        }

        parent::setUp();
    }

    protected function driver(): RemoteWebDriver
    {
        return parent::driver();
    }

    public function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            PermissionServiceProvider::class,
            TagsServiceProvider::class,
            ScoutServiceProvider::class,
            HeroiconsServiceProvider::class,
            WireUiServiceProvider::class,
            MediaLibraryServiceProvider::class,
            CalendarServiceProvider::class,
            FastPaginateProvider::class,
            QueryBuilderServiceProvider::class,
            \Laravel\Fortify\FortifyServiceProvider::class,
            FortifyServiceProvider::class,
            DataTableServiceProvider::class,
            ActivitylogServiceProvider::class,
            MediaLibraryServiceProvider::class,
            FluxServiceProvider::class,
            RouteServiceProvider::class,
            SanctumServiceProvider::class,
        ];
    }
}
