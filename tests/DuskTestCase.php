<?php

namespace FluxErp\Tests;

use Dotenv\Dotenv;
use FluxErp\FluxServiceProvider;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Providers\FortifyServiceProvider;
use FluxErp\Providers\RouteServiceProvider;
use FluxErp\Providers\SanctumServiceProvider;
use FluxErp\Providers\ViewServiceProvider;
use Hammerstone\FastPaginate\FastPaginateProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;
use Orchestra\Testbench\Dusk\TestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\Tags\TagsServiceProvider;
use Spatie\Translatable\TranslatableServiceProvider;
use TeamNiftyGmbH\Calendar\CalendarServiceProvider;
use TeamNiftyGmbH\DataTable\DataTableServiceProvider;
use WireUi\Heroicons\HeroiconsServiceProvider;
use WireUi\Providers\WireUiServiceProvider;

use function Orchestra\Testbench\package_path;

abstract class DuskTestCase extends TestCase
{
    public Model $user;

    public string $password = '#Password123';

    protected function setUp(): void
    {
        if (file_exists(__DIR__ . '/../../../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
            $dotenv->load();
        }

        parent::setUp();

        // check if database exists
        $database = config('database.connections.mysql.database');
        if (! DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'")) {
            DB::statement('CREATE DATABASE ' . $database);
        }

        if (! file_exists(public_path('flux'))) {
            symlink(package_path('public'), public_path('flux'));
        }

        $this->login();
    }

    protected function getApplicationProviders($app): array
    {
        return array_merge(parent::getApplicationProviders($app), [
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
            WebPushServiceProvider::class,
        ]);
    }

    public function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('app.debug', true);
        $app['config']->set('database.connections.mysql.database', 'laravel');
        $app['config']->set('auth.defaults.guard', 'web');
    }

    public function openMenu(): void
    {
        $this->browse(function ($browser) {
            $browser->script("Alpine.\$data(document.getElementById('main-navigation')).menuOpen = true;");
            $browser->waitForText(__('Logged in as:'));
        });
    }

    public function login(): void
    {
        $this->createLoginUser();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user->id, $this->password);
        });
    }

    public function createLoginUser(): void
    {
        $language = Language::factory()->create();

        $this->user = new User();
        $this->user->language_id = $language->id;
        $this->user->email = 'testuser@test.de';
        $this->user->firstname = 'TestUserFirstname';
        $this->user->lastname = 'TestUserLastname';
        $this->user->password = $this->password;
        $this->user->is_active = true;
        $this->user->save();
    }
}
