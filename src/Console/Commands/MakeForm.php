<?php

namespace FluxErp\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Features\SupportConsoleCommands\Commands\FormCommand;

class MakeForm extends FormCommand
{
    protected $signature = 'make:flux-form
        {name}
        {--force}
        {--createAction= }
        {--updateAction= }
        {--deleteAction= }';

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        $actionsArray = [];
        $imports = [];

        if (! is_null($this->option('createAction'))) {
            $actionsArray[] = '            \'create\' => ' . class_basename($this->option('createAction'));
            $imports[] = 'use ' . Str::beforeLast($this->option('createAction'), '::') . ';';
        }

        if (! is_null($this->option('updateAction'))) {
            $actionsArray[] = '            \'update\' => ' . class_basename($this->option('updateAction'));
            $imports[] = 'use ' . Str::beforeLast($this->option('updateAction'), '::') . ';';
        }

        if (! is_null($this->option('deleteAction'))) {
            $actionsArray[] = '            \'delete\' => ' . class_basename($this->option('deleteAction'));
            $imports[] = 'use ' . Str::beforeLast($this->option('deleteAction'), '::') . ';';
        }

        $actions = "\n" . implode(",\n", $actionsArray) . "\n        ";
        $imports = implode("\n", $imports);

        return $this->replaceNamespace($stub, $name)
            ->replacePlaceholders(
                $stub,
                $actions,
                $imports
            )
            ->replaceClass($stub, $name);
    }

    public function replacePlaceholders(&$stub, string $actions, string $imports): static
    {
        $searches = [
            ['{{ actions }}', '{{ imports }}'],
            ['{{actions}}', '{{imports}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$actions, $imports],
                $stub
            );
        }

        return $this;
    }

    public function getStub(): string
    {
        if (File::exists(base_path('stubs/livewire.form.stub'))) {
            return base_path('stubs/livewire.form.stub');
        }

        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'livewire.form.stub';
    }
}
