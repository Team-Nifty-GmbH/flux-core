<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Services\CountryService;
use Illuminate\Console\Command;

class InitCountries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Countries and fills table with data.';

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
        $countryController = new CountryService();
        $countryController->initializeCountries();

        $this->info('Countries initiated!');
    }
}
