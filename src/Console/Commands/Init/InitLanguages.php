<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Services\LanguageService;
use Illuminate\Console\Command;

class InitLanguages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:languages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Languages and fills table with data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $languageController = new LanguageService();
        $languageController->initializeLanguages();

        $this->info('Languages initiated!');
    }
}
