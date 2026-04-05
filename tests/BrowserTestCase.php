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
use Symfony\Component\Process\Process;
use TallStackUi\TallStackUiServiceProvider;
use TeamNiftyGmbH\DataTable\DataTableServiceProvider;
use Throwable;
use function Orchestra\Testbench\package_path;

abstract class BrowserTestCase extends TestCase
{
    use RefreshDatabase;

    private ?string $lastClickedTsSelect = null;

    public static function installAssets(): void
    {
        $lockFile = __DIR__ . '/../dist/.build-lock';
        $manifest = __DIR__ . '/../dist/manifest.json';

        // If manifest already exists and is valid, skip rebuilding.
        // This prevents parallel workers from rebuilding simultaneously.
        if (file_exists($manifest) && filesize($manifest) > 10) {
            $data = json_decode(file_get_contents($manifest), true);
            if (! empty($data) && isset($data['resources/js/app.js'])) {
                return;
            }
        }

        // Use file lock to ensure only one process builds at a time
        $lockDir = dirname($lockFile);
        if (! is_dir($lockDir)) {
            mkdir($lockDir, 0755, true);
        }

        $lock = fopen($lockFile, 'c');
        if (! flock($lock, LOCK_EX)) {
            fclose($lock);

            return;
        }

        try {
            // Double-check after acquiring lock (another worker may have built)
            if (file_exists($manifest) && filesize($manifest) > 10) {
                $data = json_decode(file_get_contents($manifest), true);
                if (! empty($data) && isset($data['resources/js/app.js'])) {
                    return;
                }
            }

            static::deleteDirectory(__DIR__ . '/../dist/assets/');

            if (file_exists($manifest)) {
                unlink($manifest);
            }

            $process = Process::fromShellCommandline('npm i && npm run build', __DIR__ . '/..', timeout: 180);
            $process->run();

            while ($process->isRunning()) {
                usleep(1000);
            }

            $jsFiles = glob(__DIR__ . '/../dist/assets/*.js');
            foreach ($jsFiles as $file) {
                $content = file_get_contents($file);
                if (substr($content, -1) === "\n") {
                    file_put_contents($file, rtrim($content, "\n"));
                }
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
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
        $settingsPath = __DIR__ . '/../vendor/orchestra/testbench-core/laravel/database/settings';
        if (! file_exists($settingsPath)) {
            mkdir($settingsPath, 0755, true);
        }

        parent::setUp();

        try {
            if (! file_exists(public_path('build'))) {
                symlink(package_path('dist'), public_path('build'));
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
        return 'li[role="option"]:has-text("' . $option . '")';
    }
}
