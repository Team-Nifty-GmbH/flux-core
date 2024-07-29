<?php

namespace FluxErp\Console\Commands;

use FluxErp\Contracts\OffersPrinting;
use Illuminate\Foundation\Console\ComponentMakeCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakePrintableView extends ComponentMakeCommand
{
    protected $name = 'make:printable-view';

    protected $description = 'Create a new Printable view for a class';

    public function handle(): void
    {
        $class = $this->argument('class');
        if (! in_array(OffersPrinting::class, class_implements($class))) {
            $this->error('The class must implement ' . OffersPrinting::class . ' interface.');

            return;
        }

        parent::handle();
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/printable-view.stub');
    }

    protected function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    public function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\View\Printing';
    }

    public function viewPath($path = ''): string
    {
        return str_replace('components', 'printing', parent::viewPath($path));
    }

    protected function buildClass($name): array|string
    {
        if ($this->option('inline')) {
            return str_replace(
                ['DummyView', '{{ view }}'],
                "<<<'blade'
    <div>\n    <!-- " . Inspiring::quotes()->random() . " -->\n</div>
    blade",
                parent::buildClass($name)
            );
        }

        return str_replace(
            [
                'view(\'components.' . $this->getView() . '\')',
                '{{ printableClass }}',
                '{{ printableVariable }}',
            ],
            [
                'view(\'printing.' . $this->getView() . '\')',
                Str::start($this->argument('class'), '\\'),
                lcfirst(class_basename($this->argument('class'))),
            ],
            parent::buildClass($name)
        );
    }

    protected function writeView($onSuccess = null): void
    {
        $path = $this->viewPath(
            str_replace('.', '/', 'printing.' . $this->getView()) . '.blade.php'
        );

        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->error('View already exists.');

            return;
        }

        file_put_contents(
            $path,
            '<div>
        <!-- ' . Inspiring::quotes()->random() . ' -->
    </div>'
        );

        if ($onSuccess) {
            $onSuccess();
        }
    }

    protected function getArguments(): array
    {
        return array_merge(
            parent::getArguments(),
            [
                ['class', InputOption::VALUE_NONE, 'The Printable Class'],
            ],
        );
    }
}
