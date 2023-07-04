<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
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
        $path = resource_path() . '/init-files/country-regions.json';
        if (! file_exists($path)) {
            return;
        }

        $json = json_decode(file_get_contents($path));

        if ($json->model === 'CountryRegion') {
            $jsonCountryRegions = $json->data;

            if ($jsonCountryRegions) {
                foreach ($jsonCountryRegions as $jsonCountryRegion) {
                    // Gather necessary foreign keys.
                    $countryId = Country::query()
                        ->where('iso_alpha2', $jsonCountryRegion->country_iso_alpha2)
                        ->first()
                        ?->id;

                    // Save to database, if all foreign keys are found.
                    if ($countryId) {
                        CountryRegion::query()
                            ->updateOrCreate([
                                'name' => $jsonCountryRegion->name,
                            ], [
                                'country_id' => $countryId,
                            ]);
                    }
                }
            }
        }

        $this->info('Country Regions initiated!');
    }
}
