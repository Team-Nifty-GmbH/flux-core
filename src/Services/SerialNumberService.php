<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateSerialNumberRequest;
use FluxErp\Models\SerialNumber;

class SerialNumberService
{
    public function create(array $data): SerialNumber
    {
        $serialNumber = new SerialNumber($data);
        $serialNumber->save();

        return $serialNumber;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateSerialNumberRequest(),
            service: $this,
            model: new SerialNumber()
        );

        foreach ($data as $item) {
            $serialNumber = SerialNumber::query()
                ->whereKey($item['id'])
                ->first();

            $serialNumber->fill($item);
            $serialNumber->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $serialNumber->withoutRelations()->fresh(),
                additions: ['id' => $serialNumber->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'serial numbers updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $serialNumber = SerialNumber::query()
            ->whereKey($id)
            ->first();

        if (! $serialNumber) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'serial number not found']
            );
        }

        $serialNumber->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'serial number deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        $serialNumber = SerialNumber::query()
            ->whereKey($item['id'])
            ->first();

        if (
            ($item['product_id'] ?? false) &&
            $serialNumber->product_id &&
            $serialNumber->product_id !== $item['product_id']
        ) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['product_id' => 'serial number already has a product_id'],
                additions: $response
            );
        }

        if (
            ($item['order_position_id'] ?? false) &&
            $serialNumber->order_position_id &&
            $serialNumber->order_position_id !== $item['order_position_id']
        ) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['order_position_id' => 'serial number already has an order_position_id'],
                additions: $response
            );
        }

        return null;
    }
}
