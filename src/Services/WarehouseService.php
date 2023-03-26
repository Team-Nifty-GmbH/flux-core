<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateWarehouseRequest;
use FluxErp\Models\Warehouse;

class WarehouseService
{
    public function create(array $data): Warehouse
    {
        $warehouse = new Warehouse($data);
        $warehouse->save();

        return $warehouse;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateWarehouseRequest()
        );

        foreach ($data as $item) {
            $warehouse = Warehouse::query()
                ->whereKey($item['id'])
                ->first();

            $warehouse->fill($item);
            $warehouse->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $warehouse->withoutRelations()->fresh(),
                additions: ['id' => $warehouse->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'warehouses updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $warehouse = Warehouse::query()
            ->whereKey($id)
            ->first();

        if (! $warehouse) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'warehouse not found']
            );
        }

        if ($warehouse->children()->count() > 0) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['children' => 'warehouse has children']
            );
        }

        $warehouse->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'warehouse deleted'
        );
    }
}
