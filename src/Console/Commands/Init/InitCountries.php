<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
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
        $path = resource_path() . '/init-files/countries.json';
        if (! file_exists($path)) {
            return;
        }

        $json = json_decode(file_get_contents($path));

        if ($json->model === 'Country') {
            $jsonCountries = $json->data;

            if ($jsonCountries) {
                foreach ($jsonCountries as $jsonCountry) {
                    // Gather necessary foreign keys.
                    $languageId = Language::query()
                        ->where('language_code', $jsonCountry->language_code)
                        ->first()
                        ?->id;
                    $currencyId = Currency::query()
                        ->where('iso', $jsonCountry->currency_iso)
                        ->first()
                        ?->id;

                    // Check for default country according to env 'DEFAULT_LOCALE'.
                    $isDefault = $jsonCountry->language_code === config('app.locale') &&
                        count(Country::query()
                            ->where('is_default', true)
                            ->get()) === 0;

                    // Save to database, if all foreign keys are found.
                    if ($languageId && $currencyId) {
                        Country::query()
                            ->updateOrCreate([
                                'iso_alpha2' => $jsonCountry->iso_alpha2,
                            ], [
                                'language_id' => $languageId,
                                'currency_id' => $currencyId,
                                'name' => $jsonCountry->name,
                                'iso_alpha3' => $jsonCountry->iso_alpha3,
                                'iso_numeric' => $jsonCountry->iso_numeric,
                                'is_active' => true,
                                'is_default' => $isDefault,
                                'is_eu_country' => $jsonCountry->is_eu_country,
                            ]);
                    }
                }
            }
        }

        $this->info('Countries initiated!');
    }
}
