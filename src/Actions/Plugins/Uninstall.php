<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\PluginUninstallRequest;

class Uninstall extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new PluginUninstallRequest())->rules();
    }

    public static function models(): array
    {
        return [];
    }

    public function performAction(): bool
    {
        $migrator = app('migrator');
        $paths = collect($migrator->paths());
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');

        if ($this->data['rollback'] ?? false) {
            foreach ($this->data['packages'] as $package) {
                $packageInfo = $composer->show($package);
                $migrationPaths = $paths->filter(fn ($path) => str_starts_with($path, $packageInfo['path']));

                foreach ($migrationPaths as $migrationPath) {
                    $migrator->rollback($migrationPath, ['step' => 99999]);
                }
            }
        }

        $output = '';
        $run = $composer->removePackages($this->data['packages'], false, function ($type, $buffer) use (&$output) {
            $output .= $buffer;
        });

        if (! $run) {
            throw new \RuntimeException($output);
        }

        return $run;
    }
}
