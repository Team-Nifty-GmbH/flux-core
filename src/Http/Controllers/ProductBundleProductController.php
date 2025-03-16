<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Product\ProductBundleProduct\CreateProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\DeleteProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\UpdateProductBundleProduct;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Pivots\ProductBundleProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductBundleProductController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(ProductBundleProduct::class);
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $productBundleProduct = CreateProductBundleProduct::make($request->all())
                ->validate()
                ->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 201,
                data: $productBundleProduct,
                statusMessage: 'bundle product created'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteProductBundleProduct::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'bundle product deleted'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->all();
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $productBundleProduct = UpdateProductBundleProduct::make($item)->validate()->execute(),
                    additions: ['id' => $productBundleProduct->id]
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

        return ResponseHelper::createResponseFromArrayResponse([
            'status' => $statusCode,
            'responses' => $responses,
            'statusMessage' => $statusCode === 422 ? null : 'bundle product(s) updated',
        ]);
    }
}
