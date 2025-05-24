<?php

namespace FluxErp\Livewire\Settings;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Number;
use Livewire\Component;
use Symfony\Component\Console\Output\BufferedOutput;

class System extends Component
{
    public bool $showDetails = false;

    public function render(): View
    {
        return view('flux::livewire.settings.system', [
            'systemData' => $this->getSystemData(),
        ]);
    }

    public function getSystemData(): array
    {
        // call artisan db:show with --json
        Artisan::call(
            'db:show',
            [
                '--json' => true,
                '--counts' => true,
            ],
            $output = new BufferedOutput()
        );

        return [
            'php' => [
                'version' => phpversion(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'os' => php_uname(),
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            ],
            'laravel' => [
                'version' => app()->version(),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'driver' => config('database.connections.' . config('database.default') . '.driver'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
                'details' => json_decode($output->fetch(), true),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ],
            'session' => [
                'driver' => config('session.driver'),
                'lifetime' => config('session.lifetime'),
                'secure' => config('session.secure'),
                'same_site' => config('session.same_site'),
            ],
            'queue' => [
                'connection' => config('queue.default'),
                'driver' => config('queue.connections.' . config('queue.default') . '.driver'),
                'queue' => config('queue.connections.' . config('queue.default') . '.queue'),
            ],
            'extensions' => $this->getPhpExtensions(),
            'storage' => [
                'disk_free_space' => Number::fileSize(disk_free_space('/'), 2),
                'disk_total_space' => Number::fileSize(disk_total_space('/'), 2),
                'view_cache_space' => Number::fileSize(
                    array_reduce(
                        glob(storage_path('framework/views/*')),
                        fn ($carry, $item) => $carry + (is_dir($item) ? 0 : filesize($item)),
                        0
                    ),
                    2
                ),
            ],
        ];
    }

    public function refreshSystemInfo(): void
    {
        $this->dispatch('system-refreshed');
    }

    public function toggleDetails(): void
    {
        $this->showDetails = ! $this->showDetails;
    }

    protected function getPhpExtensions(): array
    {
        $loadedExtensions = get_loaded_extensions();
        sort($loadedExtensions);

        $extensions = [];
        foreach ($loadedExtensions as $extension) {
            $extensions[strtolower($extension)] = true;
        }

        return $extensions;
    }
}
