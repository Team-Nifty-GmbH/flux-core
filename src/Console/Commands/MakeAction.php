<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\SoftDeletes;
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
            {--customName= : Custom action name}
            {--description= : Custom action description}
            {--model= : Model name}
            {--rule= : Rule name}';

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
                $name,
                $this->option('customName'),
                $this->option('description'),
                $this->option('model'),
                $this->option('rule'),
            )
            ->replaceClass($stub, $name);
    }

    protected function replacePlaceholders(
        string &$stub,
        string $name,
        string $customName = null,
        string $description = null,
        string $model = null,
        string $rule = null
    ): static {
        $searches = [
            ['{{ name }}', '{{ description }}', '{{ return }}', '{{ model }}', '{{ uses }}', '{{ rule }}'],
            ['{{name}}', '{{description}}'],
        ];

        $return = null;
        if (str_contains(strtolower($name), 'delete')) {
            $return = '?bool';
            if ($model) {
                $rule = '[\'id\' => \'required|integer|exists:' .
                    (new $model())->getTable() . ',id' .
                    (in_array(SoftDeletes::class, class_uses_recursive($model)) ? ',deleted_at,NULL' : '')
                . '\']';
            }
        }

        $uses[] = 'use FluxErp\Actions\FluxAction;';
        if ($model) {
            $uses[] = "use $model;";
        }

        if ($rule && class_exists($rule)) {
            $uses[] = "use $rule;";
        }

        sort($uses);
        $uses = implode("\r\n", $uses);

        $ruleLine = null;
        if ($rule) {
            $ruleLine = '$this->rules = ';

            if (class_exists($rule)) {
                $ruleLine .= '(new ' . class_basename($rule) . '())->rules();';
            } else {
                $ruleLine .= $rule . ';';
            }
        }

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [
                    $customName,
                    $description,
                    $return ?? ($model ? class_basename($model) : 'mixed'),
                    $model ? class_basename($model) . '::class' : '',
                    $uses,
                    $ruleLine,
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
        return $rootNamespace . '\Actions';
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
