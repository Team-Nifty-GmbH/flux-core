<?php

namespace FluxErp\Console\Commands\Cache;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'optimize')]
class OptimizeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'optimize';

    /**
     * The name of the console command.
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache the framework bootstrap files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->components->info('Caching the framework bootstrap files');

        collect([
            'events' => fn () => $this->callSilent('event:cache') == 0,
            'views' => fn () => $this->callSilent('view:cache') == 0,
            'route' => fn () => $this->callSilent('route:cache') == 0,
            'config' => fn () => $this->callSilent('config:cache') == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();

        return Command::SUCCESS;
    }
}
