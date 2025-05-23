<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeRuleset extends GeneratorCommand
{
    protected $description = 'Create a new flux ruleset';

    protected $signature = 'make:ruleset
        {name : The name of the Ruleset}
        {--model= : The model the ruleset is for}';

    protected $type = 'Ruleset';

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Rulesets' .
            ($this->option('model') ? '\\' . class_basename($this->option('model')) : '');
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/ruleset.stub');
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }
}
