<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeRuleset extends GeneratorCommand
{
    protected $signature = 'make:ruleset
        {name : The name of the Ruleset}
        {--model= : The model the ruleset is for}';

    protected $description = 'Create a new flux ruleset';

    protected $type = 'Ruleset';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/ruleset.stub');
    }

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function replaceNameAndDescription(
        string &$stub,
        ?string $name = null,
        ?string $description = null,
        ?string $model = null
    ): static {
        $searches = [
            ['{{ name }}', '{{ model }}'],
            ['{{name}}', '{{model}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$name, $description, $model],
                $stub
            );
        }

        return $this;
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Rulesets' .
            ($this->option('model') ? '\\' . class_basename($this->option('model')) : '');
    }
}
