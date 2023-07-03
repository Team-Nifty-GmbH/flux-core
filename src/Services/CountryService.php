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
}
