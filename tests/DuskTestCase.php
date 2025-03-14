<?php

namespace FluxErp\Tests;

use Dotenv\Dotenv;
use FluxErp\Console\Commands\InstallAssets;
use FluxErp\FluxServiceProvider;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Providers\BindingServiceProvider;
use FluxErp\Providers\MorphMapServiceProvider;
use FluxErp\Providers\SanctumServiceProvider;
use FluxErp\Providers\ViewServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Scout\ScoutServiceProvider;
use Livewire\LivewireServiceProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;
use Orchestra\Testbench\Dusk\TestCase;

use function Orchestra\Testbench\package_path;

use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\Tags\TagsServiceProvider;
use Spatie\Translatable\TranslatableServiceProvider;
use Spatie\TranslationLoader\TranslationServiceProvider;
use Symfony\Component\Process\Process;
use TallStackUi\TallStackUiServiceProvider;

use TeamNiftyGmbH\DataTable\DataTableServiceProvider;

abstract class DuskTestCase extends TestCase
{
    protected static string $guard = 'web';

    public string $password = '#Password123';

    public Model $user;

    protected static function deleteDirectory(string $dir): bool
    {
        if (! file_exists($dir)) {
            return true;
        }

        if (! is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (! static::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }

    protected static function installAssets(): void
    {
        static::deleteDirectory(__DIR__ . '/../public/build/assets/');

        if (file_exists($manifest = __DIR__ . '/../public/build/manifest.json')) {
            unlink($manifest);
        }

        InstallAssets::copyStubs(
            files: [
                'tailwind.config.js',
                'postcss.config.js',
                'vite.config.js',
                'package.json',
            ],
            force: true,
            basePath: fn ($path = '') => __DIR__ . '/../' . $path
        );

        // run npm i and npm run build
        $process = Process::fromShellCommandline('npm i && npm run build', timeout: 180);
        $process->run();

        // wait for process to finish
        while ($process->isRunning()) {
            usleep(1000);
        }
    }

    public static function setUpBeforeClass(): void
    {
        static::installAssets();

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        if (file_exists(__DIR__ . '/../../../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
            $dotenv->load();
        }

        parent::setUp();

        if (! file_exists(public_path('build'))) {
            symlink(package_path('public/build'), public_path('build'));
        }

        // check if database exists
        $database = config('database.connections.mysql.database');
        if (! DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'")) {
            DB::statement('CREATE DATABASE ' . $database);
        }

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->login();
    }

    public function createLoginUser(): void
    {
        $language = Language::factory()->create();

        PriceList::factory()->create([
            'is_default' => true,
        ]);

        Currency::factory()->create([
            'is_default' => true,
        ]);

        $this->user = new User();
        $this->user->language_id = $language->id;
        $this->user->email = 'testuser@test.de';
        $this->user->firstname = 'TestUserFirstname';
        $this->user->lastname = 'TestUserLastname';
        $this->user->password = $this->password;
        $this->user->is_active = true;
        $this->user->save();
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

    public function login(): void
    {
        $this->createLoginUser();

        $this->browse(function (Browser $browser): void {
            $browser->loginAs($this->user->id, static::$guard);
        });
    }

    public function openMenu(): void
    {
        $this->browse(function ($browser): void {
            $browser->script("window.Alpine.\$data(document.getElementById('main-navigation')).menuOpen = true;");
            $browser->waitForText(__('Logged in as:'));
        });
    }

    protected function getApplicationProviders($app): array
    {
        return array_merge(parent::getApplicationProviders($app), [
            TranslatableServiceProvider::class,
            TranslationServiceProvider::class,
            LivewireServiceProvider::class,
            ViewServiceProvider::class,
            PermissionServiceProvider::class,
            TagsServiceProvider::class,
            ScoutServiceProvider::class,
            TallStackUiServiceProvider::class,
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
}
