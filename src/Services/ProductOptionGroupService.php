<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;

class ProductOptionGroupService
{
    public function create(array $data): ProductOptionGroup
    {
        $productOptionGroup = new ProductOptionGroup($data);
        $productOptionGroup->save();

        return $productOptionGroup;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateProductOptionGroupRequest(),
            model: new ProductOptionGroup()
        );

        foreach ($data as $item) {
            $productOptionGroup = ProductOptionGroup::query()
                ->whereKey($item['id'])
                ->first();

            $productOptionGroup->fill($item);
            $productOptionGroup->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $productOptionGroup->withoutRelations()->fresh(),
                additions: ['id' => $productOptionGroup->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'product option groups updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $productOptionGroup = ProductOptionGroup::query()
            ->whereKey($id)
            ->first();

        if (! $productOptionGroup) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'product option group not found']
            );
        }

        if ($productOptionGroup->productOptions()->count() > 0) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['product_options' => 'product option group has product options']
            );
        }

        $productOptionGroup->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'product option group deleted'
        );
    }
}
