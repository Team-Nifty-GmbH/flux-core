<?php

namespace FluxErp\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand as BaseControllerMakeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'flux:controller')]
class ControllerMakeCommand extends BaseControllerMakeCommand
{
    protected $name = 'flux:controller';

    protected function generateFormRequests($modelClass, $storeRequestClass, $updateRequestClass): array
    {
        $storeRequestClass = 'Create' . class_basename($modelClass) . 'Request';

        $this->call('flux:request', [
            'name' => $storeRequestClass,
        ]);

        $updateRequestClass = 'Update' . class_basename($modelClass) . 'Request';

        $this->call('flux:request', [
            'name' => $updateRequestClass,
        ]);

        return [$storeRequestClass, $updateRequestClass];
    }

    public function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }
}
