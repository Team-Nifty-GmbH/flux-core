<?php

namespace FluxErp\Services;

use FluxErp\Actions\CountryRegion\CreateCountryRegion;
use FluxErp\Actions\CountryRegion\DeleteCountryRegion;
use FluxErp\Actions\CountryRegion\UpdateCountryRegion;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use Illuminate\Validation\ValidationException;

class CountryRegionService
{
    public function create(array $data): CountryRegion
    {
        return CreateCountryRegion::make($data)->execute();
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
                    data: $countryRegion = UpdateCountryRegion::make($item)->validate()->execute(),
                    additions: ['id' => $countryRegion->id]
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
            statusMessage: $statusCode === 422 ? null : 'country region(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteCountryRegion::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'country region deleted'
        );
    }

    public function initializeCountryRegions(): void
    {
        $path = resource_path() . '/init-files/country-regions.json';
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
    }
}
