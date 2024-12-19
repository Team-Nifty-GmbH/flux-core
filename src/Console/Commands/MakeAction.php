<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeAction extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action
            {name : The name of the action}
            {--model= : The model the action is for}
            {--ruleset= : The ruleset the action uses}
            {--customName= : Custom action name}
            {--description= : Custom action description}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new flux action';

    protected $type = 'Action';

    protected function getStub(): string
    {
        return $this->option('customName') || $this->option('description') ?
            $this->resolveStubPath('/stubs/action.stub') :
            $this->resolveStubPath('/stubs/action.plain.stub');
    }

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replacePlaceholders(
                $stub,
                $this->option('customName'),
                $this->option('description'),
                $this->option('model'),
                $this->option('ruleset')
            )
            ->replaceClass($stub, $name);
    }

    protected function replacePlaceholders(
        string &$stub,
        ?string $name = null,
        ?string $description = null,
        ?string $model = null,
        ?string $ruleset = null
    ): static {
        $searches = [
            [
                '{{ name }}',
                '{{ description }}',
                '{{ model }}',
                '{{ modelBaseName }}',
                '{{ ruleset }}',
                '{{ rulesetBaseName }}',
                '{{ performAction }}',
            ],
            [
                '{{name}}',
                '{{description}}',
                '{{model}}',
                '{{modelBaseName}}',
                '{{ruleset}}',
                '{{rulesetBaseName}}',
                '{{performAction}}',
            ],
        ];
        $modelBaseName = $model ? class_basename($model) : null;
        $rulesetBaseName = $ruleset ? class_basename($ruleset) : null;
        $ruleset = Str::beforeLast($ruleset, '::');

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [
                    $name,
                    $description,
                    $model,
                    $modelBaseName,
                    $ruleset,
                    $rulesetBaseName,
                    $performAction ?? '//',
                ],
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
        return $rootNamespace . '\Actions' . ($this->option('model') ? '\\' . class_basename($this->option('model')) : '');
    }

    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        if ($this->isReservedName($this->getNameInput()) || $this->didReceiveOptions($input)) {
            return;
        }

        $customName = $this->components->ask('What custom name should it have?', 'none');
        $description = $this->components->ask('What custom description should it have?', 'none');

        if ($customName && $customName !== 'none') {
            $input->setOption('customName', $customName);
        }

        if ($description && $description !== 'none') {
            $input->setOption('description', $description);
        }
    }
}
