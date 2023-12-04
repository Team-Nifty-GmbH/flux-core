<?php

namespace FluxErp\Console\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand as BaseModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\multiselect;

#[AsCommand(name: 'flux:model')]
class ModelMakeCommand extends BaseModelMakeCommand
{
    protected $name = 'flux:model';

    public function handle(): void
    {
        parent::handle();

        if ($this->option('actions')) {
            $this->createActions();
        }

        if ($this->option('data-table')) {
            $this->createDataTable();
        }

        if ($this->option('livewire-form')) {
            $this->createLivewireForm();
        }
    }

    protected function getOptions(): array
    {
        return array_merge(
            parent::getOptions(),
            [
                ['actions', 'A', InputOption::VALUE_NONE, 'Create actions for the model'],
                ['data-table', 'd', InputOption::VALUE_NONE, 'Create data-table for the model'],
                ['livewire-form', 'l', InputOption::VALUE_NONE, 'Create Livewire form class for the model'],
            ]
        );
    }

    protected function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('flux:controller', array_filter([
            'name' => "{$controller}Controller",
            '--model' => $modelName,
            '--api' => true,
            '--requests' => true,
            '--test' => true,
        ]));
    }

    protected function createActions(): void
    {
        $action = Str::studly(class_basename($this->argument('name')));
        $requestNamespace = $this->rootNamespace() . 'Http\Requests\\';

        $this->call('make:action', [
            'name' => $action . DIRECTORY_SEPARATOR . Str::start('Create', $action),
            '--model' => $this->qualifyClass($this->argument('name')),
            '--rule' => $this->option('requests') ? $requestNamespace . 'Create' . $action . 'Request' : null,
        ]);
        $this->call('make:action', [
            'name' => $action . DIRECTORY_SEPARATOR . Str::start('Update', $action),
            '--model' => $this->qualifyClass($this->argument('name')),
            '--rule' => $this->option('requests') ? $requestNamespace . 'Update' . $action . 'Request' : null,
        ]);
        $this->call('make:action', [
            'name' => $action . DIRECTORY_SEPARATOR . Str::start('Delete', $action),
            '--model' => $this->qualifyClass($this->argument('name')),
        ]);
    }

    protected function createDataTable(): void
    {
        $dataTable = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('make:data-table', [
            'name' => Str::finish($dataTable, 'List'),
            'model' => $modelName,
        ]);
    }

    protected function createLivewireForm(): void
    {
        $livewireForm = Str::studly(class_basename($this->argument('name')));

        $this->call('livewire:form', [
            'name' => Str::finish($livewireForm, 'Form'),
        ]);
    }

    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        if ($this->isReservedName($this->getNameInput()) || $this->didReceiveOptions($input)) {
            return;
        }

        collect(multiselect('Would you like any of the following?', [
            'seed' => 'Database Seeder',
            'factory' => 'Factory',
            'requests' => 'Form Requests',
            'migration' => 'Migration',
            'actions' => 'Actions',
            'data-table' => 'Data Table',
            'livewire-form' => 'Livewire Form',
            'resource' => 'Resource Controller',
        ]))->each(fn ($option) => $input->setOption($option, true));
    }
}
