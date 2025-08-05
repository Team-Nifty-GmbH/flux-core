<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use Illuminate\Console\Command;

class InitCountryRegions extends Command
{
    protected $description = 'Initiates Country Regions and fills table with data.';

    protected $signature = 'init:country-regions';

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
                    $countryId = resolve_static(Country::class, 'query')
                        ->where('iso_alpha2', $jsonCountryRegion->country_iso_alpha2)
                        ->first()
                        ?->id;

                    // Save to database, if all foreign keys are found.
                    if ($countryId) {
                        resolve_static(CountryRegion::class, 'query')
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
