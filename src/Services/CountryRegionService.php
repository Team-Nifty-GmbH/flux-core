<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateCountryRegionRequest;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;

class CountryRegionService
{
    public function create(array $data): CountryRegion
    {
        $countryRegion = new CountryRegion($data);
        $countryRegion->save();

        return $countryRegion;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateCountryRegionRequest(),
            model: new CountryRegion()
        );

        foreach ($data as $item) {
            // Find existing data to update.
            $countryRegion = CountryRegion::query()
                ->whereKey($item['id'])
                ->first();

            // Save new data to table.
            $countryRegion->fill($item);
            $countryRegion->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $countryRegion->withoutRelations()->fresh(),
                additions: ['id' => $countryRegion->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'country regions updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $countryRegion = CountryRegion::query()
            ->whereKey($id)
            ->first();

        if (! $countryRegion) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'country region not found']
            );
        }

        $countryRegion->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'country region deleted'
        );
    }
}
