<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateProductPropertyRequest;
use FluxErp\Models\ProductProperty;

class ProductPropertyService
{
    public function create(array $data): ProductProperty
    {
        $productProperty = new ProductProperty($data);
        $productProperty->save();

        return $productProperty;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateProductPropertyRequest(),
            model: new ProductProperty()
        );

        foreach ($data as $item) {
            $productProperty = ProductProperty::query()
                ->whereKey($item['id'])
                ->first();

            $productProperty->fill($item);
            $productProperty->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $productProperty->withoutRelations()->fresh(),
                additions: ['id' => $productProperty->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'product properties updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $productProperty = ProductProperty::query()
            ->whereKey($id)
            ->first();

        if (! $productProperty) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'product property not found']
            );
        }

        if ($productProperty->products()->count() > 0) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['products' => 'product property has products']
            );
        }

        $productProperty->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'product property deleted'
        );
    }
}
