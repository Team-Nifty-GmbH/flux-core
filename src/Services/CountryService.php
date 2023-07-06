<?php

namespace FluxErp\Services;

use FluxErp\Actions\Country\CreateCountry;
use FluxErp\Actions\Country\DeleteCountry;
use FluxErp\Actions\Country\UpdateCountry;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use Illuminate\Validation\ValidationException;

class CountryService
{
    public function create(array $data): Country
    {
        return CreateCountry::make($data)->execute();
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $country = UpdateCountry::make($item)->validate()->execute(),
                    additions: ['id' => $country->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $e->errors(),
                    additions: [
                        'id' => array_key_exists('id', $item) ? $item['id'] : null,
                    ]
                );

                unset($data[$key]);
            }
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'countries updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteCountry::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'country deleted'
        );
    }

    public function initializeCountries(): void
    {
        $path = resource_path() . '/init-files/countries.json';
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
    }
}
