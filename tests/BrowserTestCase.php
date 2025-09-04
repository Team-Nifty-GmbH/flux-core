<?php

namespace FluxErp\Tests;

use FluxErp\Console\Commands\InstallAssets;
use FluxErp\FluxServiceProvider;
use FluxErp\Providers\BindingServiceProvider;
use FluxErp\Providers\MorphMapServiceProvider;
use FluxErp\Providers\SanctumServiceProvider;
use FluxErp\Providers\ViewServiceProvider;
use Illuminate\Database\Eloquent\Model;
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
use Symfony\Component\Process\Process;
use TallStackUi\TallStackUiServiceProvider;
use TeamNiftyGmbH\DataTable\DataTableServiceProvider;
use Throwable;
use function Orchestra\Testbench\package_path;

abstract class BrowserTestCase extends TestCase
{
    protected static string $guard = 'web';

    public string $password = '#Password123';

    public Model $user;

    public static function installAssets(): void
    {
        static::deleteDirectory(__DIR__ . '/../public/build/assets/');

        if (file_exists($manifest = __DIR__ . '/../public/build/manifest.json')) {
            unlink($manifest);
        }

        $testbenchConfigPath = __DIR__ . '/../vendor/orchestra/testbench-core/laravel/tailwind.config.mjs';
        if (file_exists($testbenchConfigPath)) {
            $stubContent = file_get_contents(__DIR__ . '/../stubs/tailwind/tailwind.config.mjs');

            $configContent = str_replace(
                '{{ relative_path }}',
                '../../../..',
                $stubContent
            );

            $configContent = str_replace(
                '].concat(dataTablesConfig.content, fluxConfig.content),',
                ',\n        \'../../../../resources/**/*.blade.php\',\n        \'../../../../resources/**/*.js\',\n        \'../../../../src/**/*.php\',\n    ].concat(dataTablesConfig.content, fluxConfig.content),',
                $configContent
            );

            file_put_contents($testbenchConfigPath, $configContent);
        }

        InstallAssets::copyStubs(
            force: true,
            basePath: fn ($path = '') => __DIR__ . '/../' . $path
        );

        $process = Process::fromShellCommandline('npm i && npm run build', timeout: 180);
        $process->run();

        while ($process->isRunning()) {
            usleep(1000);
        }

        $jsFiles = glob(__DIR__ . '/../public/build/assets/*.js');
        foreach ($jsFiles as $file) {
            $content = file_get_contents($file);
            if (substr($content, -1) === "\n") {
                file_put_contents($file, rtrim($content, "\n"));
            }
        }
    }

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
}
