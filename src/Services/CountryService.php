<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateCountryRequest;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CountryService
{
    public function create(array $data): Country
    {
        $country = new Country($data);
        $country->save();

        return $country;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateCountryRequest(),
            model: new Country()
        );

        foreach ($data as $item) {
            $country = Country::query()
                ->whereKey($item['id'])
                ->first();

            // Save new data to table.
            $country->fill($item);
            $country->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $country->withoutRelations()->fresh(),
                additions: ['id' => $country->id]
            );
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
        $country = Country::query()
            ->whereKey($id)
            ->first();

        if (! $country) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'country not found']
            );
        }

        // Don't delete if in use.
        if ($country->addresses()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['address' => 'country referenced by an address']
            );
        }

        if ($country->clients()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['client' => 'country referenced by a client']
            );
        }

        // Also delete all child country regions.
        $country->regions()->delete();

        // Rename unique columns on soft-delete.
        $country->iso_alpha2 = $country->iso_alpha2 . '___' . Hash::make(Str::uuid());
        $country->save();
        $country->delete();

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
