<?php

namespace FluxErp\Console\Commands;

use Illuminate\Foundation\Console\RequestMakeCommand as BaseRequestMakeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'flux:request')]
class RequestMakeCommand extends BaseRequestMakeCommand
{
    protected $name = 'flux:request';

    public function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }
}
