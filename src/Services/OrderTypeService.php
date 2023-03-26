<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateOrderTypeRequest;
use FluxErp\Models\OrderType;

class OrderTypeService
{
    public function create(array $data): OrderType
    {
        $orderType = new OrderType($data);
        $orderType->save();

        return $orderType;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateOrderTypeRequest(),
            model: new OrderType()
        );

        foreach ($data as $item) {
            // Find existing data to update.
            $orderType = OrderType::query()
                ->whereKey($item['id'])
                ->first();

            // Save new data to table.
            $orderType->fill($item);
            $orderType->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $orderType->withoutRelations()->fresh(),
                additions: ['id' => $orderType->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'order types updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $orderType = OrderType::query()
            ->whereKey($id)
            ->first();

        if (! $orderType) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'order type not found']
            );
        }

        $orderType->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'order type deleted'
        );
    }
}
