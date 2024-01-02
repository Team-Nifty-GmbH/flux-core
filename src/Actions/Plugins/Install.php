<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\PluginInstallRequest;

class Install extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new PluginInstallRequest())->rules();
    }

    public static function models(): array
    {
        return [];
    }

    public function performAction(): bool
    {
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');
        $output = null;

        $command = array_merge(
            $this->data['packages'],
            ['--no-progress', '--no-interaction', '--no-suggest', '--no-ansi'],
            $this->data['options'] ?? []
        );
        $run = $composer->requirePackages($command, false, function ($type, $buffer) use (&$output) {
            $output .= $buffer;
        });

        if (! $run) {
            throw new \RuntimeException($output);
        }

        $migrator = app('migrator');
        $paths = collect($migrator->paths());
        if ($this->data['migrate'] ?? true) {
            foreach ($this->data['packages'] as $package) {
                $packageInfo = $composer->show($package);
                $migrationPaths = $paths->filter(fn ($path) => str_starts_with($path, $packageInfo['path']));

                foreach ($migrationPaths as $migrationPath) {
                    $migrator->run($migrationPath);
                }
            }
        }

        return $run;
    }
}
