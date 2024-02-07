<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Actions\FluxAction;

abstract class BasePluginAction extends FluxAction
{
    public static function models(): array
    {
        return [];
    }

    protected static function migrate(array $packages, bool $rollback = false): void
    {
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');
        $migrator = app('migrator');
        $paths = collect($migrator->paths());

        foreach ($packages as $package) {
            $packageInfo = $composer->show($package);
            $migrationPaths = $paths->filter(fn ($path) => str_starts_with($path, $packageInfo['path']));

            foreach ($migrationPaths as $migrationPath) {
                if (! $rollback) {
                    $migrator->run($migrationPath);
                } else {
                    $migrator->rollback($migrationPath, ['step' => 99999]);
                }
            }
        }
    }
}
