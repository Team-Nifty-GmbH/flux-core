<?php

namespace FluxErp\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class InstallAssets extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flux:install-assets
        {directory? : The directory to install the assets}
        {--force : Overwrite existing files}
        {--merge-json : If a file is json parsable merge it recursively}
        {--no-build : Skip building the assets}';

    public static function copyStubs(
        ?array $files = null,
        bool $force = false,
        bool $merge = true,
        ?Closure $basePath = null
    ): void {
        $files = is_array($files)
            ? $files
            : [
                __DIR__ . '/../../../package.json',
            ];

        if (! $basePath) {
            $basePath = fn ($path = '') => base_path($path);
        }

        foreach ($files as $file) {
            if (file_exists($basePath($file)) && ! $force && ! $merge) {
                continue;
            }

            $merged = false;
            $oldContent = null;
            if (file_exists($basePath($file))) {
                $oldContent = file_get_contents($basePath($file));
            }

            $content = file_get_contents($file);
            $content = str_replace(
                '{{ relative_path }}',
                substr(realpath(__DIR__ . '/../../../'), strlen($basePath())),
                $content
            );

            if ($merge && $oldContent) {
                // if the file is json parsable merge it recursively
                $decodedContent = json_decode(
                    str_replace('.', '__dot__', $content),
                    true
                );
                $decodedOldContent = json_decode(
                    str_replace('.', '__dot__', $oldContent),
                    true
                );

                if (is_array($decodedContent) && is_array($decodedOldContent)) {
                    $content = str_replace(
                        '__dot__',
                        '.',
                        json_encode(
                            Arr::undot(
                                array_merge(
                                    Arr::dot($decodedContent),
                                    Arr::dot($decodedOldContent)
                                )
                            ),
                            JSON_PRETTY_PRINT
                        )
                    );

                    $merged = true;
                }
            }

            if ($merged || $force) {
                file_put_contents($basePath($file), $content);
            }
        }
    }

    protected static function updateNodePackages(callable $callback, $dev = true): void
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->mergeAssets(
            resource_path('js/app.js'),
            __DIR__ . '/../../../stubs/app.js.stub',
            'import \'./bootstrap\';'
        );
        $this->mergeAssets(
            resource_path('css/app.css'),
            __DIR__ . '/../../../stubs/app.css.stub',
            '@import \'tailwindcss\';'
        );

        $this->callSilent('storage:link');

        // require npm packages
        $this->info('Installing npm packages...');

        static::copyStubs(
            force: $this->option('force'),
            merge: $this->option('merge-json'),
        );

        $this->updateNodePackages(function ($packages) {
            return data_get(
                json_decode(
                    file_get_contents(__DIR__ . '/../../../package.json'),
                    true
                ),
                'devDependencies'
            ) + $packages;
        });

        if (file_exists(resource_path('views/welcome.blade.php'))) {
            unlink(resource_path('views/welcome.blade.php'));
        }

        if (! file_exists(resource_path('views/.gitkeep'))) {
            file_put_contents(resource_path('views/.gitkeep'), '');
        }

        if (file_exists(database_path('/migrations/0001_01_01_000000_create_users_table.php'))) {
            unlink(database_path('/migrations/0001_01_01_000000_create_users_table.php'));
        }

        if (file_exists(database_path('/migrations/0001_01_01_000001_create_cache_table.php'))) {
            unlink(database_path('/migrations/0001_01_01_000001_create_cache_table.php'));
        }

        if (file_exists(database_path('/migrations/0001_01_01_000002_create_jobs_table.php'))) {
            unlink(database_path('/migrations/0001_01_01_000002_create_jobs_table.php'));
        }

        $commands = ['npm install'];
        if (! $this->option('no-build')) {
            $commands[] = 'npm run build';
        }

        $commands[] = 'artisan tallstackui:ide';

        $this->runCommands($commands);
    }

    public function mergeAssets(string $path, string $stubPath, string $insertAfter): void
    {
        if (! file_exists($path)) {
            file_put_contents($path, '');
        }

        $currentContent = file_get_contents($path);
        $stubContent = Str::of(file_get_contents($stubPath))->start("\n");
        $manageStart = '/* --- START - Managed by Flux - do not edit --- */';
        $manageEnd = '/* --- END - Managed by Flux - do not edit --- */';

        // Check if managed section already exists
        if (
            str_contains($currentContent, $manageStart)
            && str_contains($currentContent, $manageEnd)
            && $current = Str::between($currentContent, $manageStart, $manageEnd)
        ) {
            // Replace existing managed content
            $currentContent = Str::replace(
                "$current",
                "$stubContent",
                $currentContent
            );
        } else {
            // Insert new managed section
            if (str_contains($currentContent, $insertAfter)) {
                $currentContent = str_replace(
                    $insertAfter,
                    "$insertAfter\n\n$manageStart\n" . $stubContent . "\n$manageEnd",
                    $currentContent
                );
            } else {
                // Fallback: append to end of file
                $currentContent .= "\n\n$manageStart\n" . $stubContent . "\n$manageEnd";
            }
        }

        file_put_contents($path, $currentContent);
    }

    protected function runCommands($commands): void
    {
        $process = Process::fromShellCommandline(
            implode(' && ', $commands),
            null,
            null,
            null,
            180
        );

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> ' . $e->getMessage() . PHP_EOL);
            }
        }

        $process->run(function ($type, $line): void {
            $this->output->write('    ' . $line);
        });
    }
}
