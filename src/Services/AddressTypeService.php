<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateAddressRequest;
use FluxErp\Models\AddressType;
use FluxErp\Models\Client;

class AddressTypeService
{
    public function create(array $data): AddressType
    {
        $addressType = new AddressType($data);
        $addressType->save();

        return $addressType;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateAddressRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $addressType = AddressType::query()
                ->whereKey($item['id'])
                ->first();

            $addressType->fill($item);
            $addressType->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $addressType->withoutRelations()->fresh(),
                additions: ['id' => $addressType->id]
            );
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
        $addressType = AddressType::query()
            ->whereKey($id)
            ->first();

        if (! $addressType) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'address type not found']
            );
        }

        if ($addressType->is_lock) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['is_locked' => 'address type is locked']
            );
        }

        if ($addressType->addresses()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['address' => 'address type has attached addresses']
            );
        }

        $addressType->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'address type deleted'
        );
    }
}
