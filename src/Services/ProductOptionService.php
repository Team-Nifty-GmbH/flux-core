<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateProductOptionRequest;
use FluxErp\Models\ProductOption;

class ProductOptionService
{
    public function create(array $data): ProductOption
    {
        $productOption = new ProductOption($data);
        $productOption->save();

        return $productOption;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateProductOptionRequest(),
            model: new ProductOption()
        );

        foreach ($data as $item) {
            $productOption = ProductOption::query()
                ->whereKey($item['id'])
                ->first();

            $productOption->fill($item);
            $productOption->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $productOption->withoutRelations()->fresh(),
                additions: ['id' => $productOption->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'product options updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $productOption = ProductOption::query()
            ->whereKey($id)
            ->first();

        if (! $productOption) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'product option not found']
            );
        }

        $productOption->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'product option deleted'
        );
    }
}
