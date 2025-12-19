<?php

namespace FluxErp\Tests;

use FluxErp\FluxServiceProvider;
use FluxErp\Providers\BindingServiceProvider;
use FluxErp\Providers\MorphMapServiceProvider;
use FluxErp\Providers\SanctumServiceProvider;
use FluxErp\Providers\ViewServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\Tags\TagsServiceProvider;
use Spatie\Translatable\TranslatableServiceProvider;
use Spatie\TranslationLoader\TranslationServiceProvider;
use TallStackUi\TallStackUiServiceProvider;
use TeamNiftyGmbH\DataTable\DataTableServiceProvider;
use Throwable;
use function Orchestra\Testbench\package_path;

abstract class BrowserTestCase extends TestCase
{
    use RefreshDatabase;

    private ?string $lastClickedTsSelect = null;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            if (! file_exists(public_path('build'))) {
                symlink(package_path('public/build'), public_path('build'));
            }
        } catch (Throwable) {
        }

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('app.debug', true);
        $app['config']->set('database.connections.mysql.database', 'testing');
        $app['config']->set('auth.defaults.guard', 'web');
        $app['config']->set('flux.install_done', true);
        $app['config']->set('session.driver', 'file');
    }

    protected function getApplicationProviders($app): array
    {
        return array_merge(parent::getApplicationProviders($app), [
            TranslatableServiceProvider::class,
            TranslationServiceProvider::class,
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
        ]);
    }

    protected function tsSelect(string $wireModel): string
    {
        return $this->lastClickedTsSelect = '//div[contains(@x-data, "' . $wireModel . '")]//button[@x-ref="button"]';
    }

    protected function tsSelectOption(string $option): string
    {
        $base = '//li[@role="option"][contains(., "' . $option . '")]';

        return ! $this->lastClickedTsSelect
            ? $base
            : $this->lastClickedTsSelect . '/../..//ul' . $base;
    }
}
