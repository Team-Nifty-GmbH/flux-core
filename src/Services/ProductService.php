<?php

namespace FluxErp\Services;

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\DeleteProduct;
use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function create(array $data): Product
    {
        return CreateProduct::make($data)->validate()->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeleteProduct::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'product deleted'
        );
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
                    data: $product = UpdateProduct::make($item)->validate()->execute(),
                    additions: ['id' => $product->id]
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
            statusMessage: $statusCode === 422 ? null : 'product(s) updated',
            bulk: true
        );
    }
}
