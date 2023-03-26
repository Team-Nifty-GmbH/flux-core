<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install {customComposerCommand?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Composer install with Modules composer dependencies from Modules composer.lock files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modules = file_get_contents('modules_statuses.json');
        if (! $modules) {
            $this->error('File: \'modules_statuses.json\' not found!');

            return 1;
        }

        $modules = Module::all();
        $moduleStatuses = [];

        if (count($modules) > 0) {
            try {
                $lockFile = file_get_contents('composer.lock');
            } catch (\Exception $e) {
                $this->error('File: \'composer.lock\' not found!');

                return 2;
            }

            //Move composer.lock
            rename('composer.lock', 'composer.lock.bak');

            $lock = json_decode(json: $lockFile, flags: JSON_UNESCAPED_SLASHES);

            foreach ($modules as $module => $value) {
                try {
                    $moduleLockFile = file_get_contents('modules/' . $module . '/composer.lock');
                } catch (\Exception $e) {
                    $this->error('File: \'modules/' . $module . '/composer.lock\' not found!');

                    continue;
                }

                $moduleStatuses[$module] = $value->isEnabled();
                $value->disable();
                $moduleLock = json_decode(json: $moduleLockFile, flags: JSON_UNESCAPED_SLASHES)->packages;

                foreach ($moduleLock as $item) {
                    $exists = false;
                    foreach ($lock->packages as $package) {
                        if ($package->name === $item->name) {
                            if ($package->version !== $item->version) {
                                $warning = 'Package versions differ: ' .
                                    'Module: \'' . $module . '\' ' .
                                    'Package: \'' . $package->name . '\' ' .
                                    'Installed Version: \'' . $package->version . '\' ' .
                                    'Module Version: \'' . $item->version . '\'';

                                $this->warn($warning);

                                Log::warning($warning, [
                                    'module' => $item->name,
                                    'package' => $package->name,
                                    'installed_version' => $package->version,
                                    'module_version' => $item->version,
                                ]);
                            }

                            $exists = true;
                            break;
                        }
                    }

                    if (! $exists) {
                        $lock->packages[] = $item;
                    }
                }
            }

            $newLockFile = fopen('composer.lock', 'w');
            fwrite($newLockFile, json_encode($lock, JSON_UNESCAPED_SLASHES));
            fclose($newLockFile);
        }

        $this->newLine();

        if (! $this->argument('customComposerCommand')) {
            $result = exec('composer i' .
                (
                    config('app.env') === 'production' ?
                        ' --no-interaction --no-dev --no-ansi --no-plugins --no-progress' :
                        ''
                ),
                result_code: $errorCode
            );
        } else {
            $result = exec($this->argument('customComposerCommand'), result_code: $errorCode);
        }

        $this->newLine();

        if (count($modules) > 0) {
            rename('composer.lock.bak', 'composer.lock');
        }

        if (! $result) {
            $this->error((! $this->argument('customComposerCommand') ?
                    'Composer install' : $this->argument('customComposerCommand')) .
                ' failed with error code: ' . $errorCode);

            return 3;
        }

        foreach ($moduleStatuses as $module => $moduleStatus) {
            if ($moduleStatus) {
                Module::find($module)?->enable();
            }
        }

        $this->info('All Modules dependencies have been installed!');

        return Command::SUCCESS;
    }
}
