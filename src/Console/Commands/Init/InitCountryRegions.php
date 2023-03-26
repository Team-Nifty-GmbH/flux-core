<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Services\CountryRegionService;
use Illuminate\Console\Command;

class InitCountryRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:country-regions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Country Regions and fills table with data.';

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
        $countryRegionController = new CountryRegionService();
        $countryRegionController->initializeCountryRegions();

        $this->info('Country Regions initiated!');
    }
}
