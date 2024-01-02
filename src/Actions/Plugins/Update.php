<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\PluginUpdateRequest;

class Update extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new PluginUpdateRequest())->rules();
    }

    public static function models(): array
    {
        return [];
    }

    public function performAction(): mixed
    {
        $composer = app('composer');

        $composer->updatePackages($this->data['packages']);

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

        return app('composer')->updatePackages($this->data[0] ?? null);
    }
}
