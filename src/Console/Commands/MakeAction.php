<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeAction extends GeneratorCommand
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new flux action';

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

    protected $type = 'Action';

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

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replacePlaceholders(
                $stub,
                $name,
                $this->option('customName'),
                $this->option('description'),
                $this->option('model'),
                $this->option('ruleset')
            )
            ->replaceClass($stub, $name);
    }

    protected function generatePerformAction(string $name, string $modelBaseName): ?string
    {
        $pureName = Str::afterLast($name, '\\');

        if (! str_starts_with($pureName, 'Create')
            && ! str_starts_with($pureName, 'Update')
            && ! str_starts_with($pureName, 'Delete')
        ) {
            return null;
        }

        $variableName = Str::of($pureName)->after('Create')->lcfirst();

        if (str_starts_with($pureName, 'Create')) {
            return <<<PHP
        \${$variableName} = app($modelBaseName::class, ['attributes' => \$this->getData()]);
                \${$variableName}->save();

                return \${$variableName}->fresh();
        PHP;
        }

        if (str_starts_with($pureName, 'Update')) {
            return <<<PHP
        \${$variableName} = resolve_static($modelBaseName::class, 'query')
                    ->whereKey(\$this->getData('id'))
                    ->first();
                \${$variableName}->fill(\$this->getData());
                \${$variableName}->save();

                return \${$variableName}->withoutRelations()->fresh();
        PHP;
        }

        if (str_starts_with($pureName, 'Delete')) {
            return <<<PHP
        return resolve_static($modelBaseName::class, 'query')
                    ->whereKey(\$this->getData('id'))
                    ->first()
                    ->delete();
        PHP;
        }

        return null;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Actions' . ($this->option('model') ? '\\' . class_basename($this->option('model')) : '');
    }

    protected function getStub(): string
    {
        return $this->option('customName') || $this->option('description') ?
            $this->resolveStubPath('/stubs/action.stub') :
            $this->resolveStubPath('/stubs/action.plain.stub');
    }

    protected function replacePlaceholders(
        string &$stub,
        string $name,
        ?string $customName = null,
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
                '{{ returnType }}',
            ],
            [
                '{{name}}',
                '{{description}}',
                '{{model}}',
                '{{modelBaseName}}',
                '{{ruleset}}',
                '{{rulesetBaseName}}',
                '{{performAction}}',
                '{{returnType}}',
            ],
        ];
        $modelBaseName = $model ? class_basename($model) : null;
        $ruleset = Str::beforeLast($ruleset, '::');
        $rulesetBaseName = $ruleset ? class_basename($ruleset) : null;
        $performAction = $this->generatePerformAction($name, $modelBaseName);

        $returnType = null;
        if (str_starts_with(Str::afterLast($name, '\\'), 'Delete')) {
            $returnType = 'bool';
        }

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [
                    $customName,
                    $description,
                    $model,
                    $modelBaseName,
                    $ruleset,
                    $rulesetBaseName,
                    $performAction ?? '//',
                    $returnType ?? $modelBaseName,
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
}
