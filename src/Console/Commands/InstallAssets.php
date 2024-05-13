<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Process\Process;

class InstallAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flux:install-assets {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->callSilent('storage:link');

        // require npm packages
        $this->info('Installing npm packages...');

        // Tailwind Configuration...
        if (! file_exists(base_path('tailwind.config.js')) || $this->option('force')) {
            $this->info('Publishing Tailwind Configuration...');
            copy(__DIR__ . '/../../../stubs/tailwind/tailwind.config.js', base_path('tailwind.config.js'));
        }

        if (! file_exists(base_path('postcss.config.js')) || $this->option('force')) {
            $this->info('Publishing PostCSS Configuration...');
            copy(__DIR__ . '/../../../stubs/tailwind/postcss.config.js', base_path('postcss.config.js'));
        }

        if (! file_exists(base_path('vite.config.js')) || $this->option('force')) {
            $this->info('Publishing Vite Configuration...');
            copy(__DIR__ . '/../../../stubs/tailwind/vite.config.js', base_path('vite.config.js'));
        }

        $this->updateNodePackages(function ($packages) {
            return [
                '@fontsource/inter' => '^5.0.18',
                '@fullcalendar/core' => '^6.1.11',
                '@fullcalendar/daygrid' => '^6.1.10',
                '@fullcalendar/interaction' => '^6.1.10',
                '@fullcalendar/list' => '^6.1.11',
                '@fullcalendar/timegrid' => '^6.1.10',
                '@tailwindcss/forms' => '^0.5.7',
                '@tailwindcss/typography' => '^0.5.10',
                'autoprefixer' => '^10.4.16',
                'postcss' => '^8.4.32',
                'tailwindcss' => '^3.4.0',
                'tributejs' => '^5.1.3',
            ] + $packages;
        });

        if (file_exists(resource_path('views/welcome.blade.php'))) {
            unlink(resource_path('views/welcome.blade.php'));
        }

        $this->runCommands(['npm install', 'npm run build']);
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

    protected function runCommands($commands): void
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> ' . $e->getMessage() . PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    ' . $line);
        });
    }
}
