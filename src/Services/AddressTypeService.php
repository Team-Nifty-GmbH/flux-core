<?php

namespace FluxErp\Services;

use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
use Illuminate\Validation\ValidationException;

class AddressTypeService
{
    public function create(array $data): AddressType
    {
        return CreateAddressType::make($data)->execute();
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
                    data: $addressType = UpdateAddressType::make($item)->validate()->execute(),
                    additions: ['id' => $addressType->id]
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
            statusMessage: $statusCode === 422 ? null : 'address type updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteAddressType::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'warehouse deleted'
        );
    }

    public function initialize(): void
    {
        $path = resource_path() . '/init-files/address-types.json';
        $json = json_decode(file_get_contents($path), true);

        if ($json['model'] === 'AddressType') {
            $jsonAddressTypes = $json['data'];

            if ($jsonAddressTypes) {
                foreach (Client::all() as $client) {
                    foreach ($jsonAddressTypes as $jsonAddressType) {
                        $data = array_map(function ($value) {
                            return __($value);
                        }, $jsonAddressType);
                        $data['client_id'] = $client->id;

                        // Gather necessary foreign keys.
                        $addressType = AddressType::query()
                            ->where('address_type_code', $data['address_type_code'])
                            ->where('client_id', $client->id)
                            ->firstOrNew();

                        if (! $addressType->exists) {
                            $addressType->fill($data);
                            $addressType->save();
                        }
                    }
                }
            }
        }
    }
}
