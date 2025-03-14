<?php

namespace FluxErp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeWidget extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Livewire component implementing the UserWidget contract';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:widget {name : The name of the Livewire component}';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $className = Str::studly($name);
        $kebabCaseClassName = Str::kebab($className);

        $stub = $this->getStubContents();
        $stub = str_replace(['{{class}}', '{{kebab-case class}}'], [$className, $kebabCaseClassName], $stub);

        $fileName = $className . '.php';
        $filePath = app_path('Livewire/Widgets/' . $fileName);

        $viewPath = resource_path('views/livewire/widgets/' . $kebabCaseClassName . '.blade.php');
        $viewContents = '<div></div>';

        File::ensureDirectoryExists(pathinfo($filePath, PATHINFO_DIRNAME));
        File::ensureDirectoryExists(pathinfo($viewPath, PATHINFO_DIRNAME));

        $filesystem = new Filesystem();
        $filesystem->ensureDirectoryExists(app_path('Http/Livewire/Widgets'));
        $filesystem->put($filePath, $stub);
        $filesystem->put($viewPath, $viewContents);

        $this->info('Widget component created successfully: ' . $fileName);
        $this->info('Widget view created successfully: ' . $viewPath);
    }

    private function getStubContents(): false|string
    {
        return file_get_contents(__DIR__ . '/stubs/UserWidgetComponent.stub');
    }
}
