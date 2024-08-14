<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\GeneratorCommand;
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
            ->replaceNameAndDescription($stub, $this->option('customName'), $this->option('description'))
            ->replaceClass($stub, $name);
    }

    protected function replaceNameAndDescription(string &$stub, ?string $name = null, ?string $description = null): static
    {
        $searches = [
            ['{{ name }}', '{{ description }}'],
            ['{{name}}', '{{description}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$name, $description],
                $stub
            );
        }

        return $this;
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Actions';
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
