<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\ProductOptionGroup\CreateProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\DeleteProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\UpdateProductOptionGroup;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductOptionGroupController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new ProductOptionGroup();
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $productOptionGroup = CreateProductOptionGroup::make($request->all())->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $productOptionGroup,
            statusMessage: 'product option group created'
        );
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->all();
        if (! array_is_list($data)) {
            $data = [$request->all()];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $productOptionGroup = UpdateProductOptionGroup::make($item)->validate()->execute(),
                    additions: ['id' => $productOptionGroup->id]
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

        $bulk = count($responses) > 1;

        return ! $bulk ?
            ResponseHelper::createResponseFromArrayResponse(
                array_merge(
                    array_shift($responses),
                    ['statusMessage' => 'product option group updated']
                )
            ) :
            ResponseHelper::createResponseFromBase(
                statusCode: $statusCode,
                data: $responses,
                statusMessage: $statusCode === 422 ? null : 'product option group(s) updated',
                bulk: true
            );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            $response = DeleteProductOptionGroup::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        $responses = [];
        if (is_array($response)) {
            foreach ($response['product_options'] as $productOption => $success) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: $success ? 200 : 409,
                    data: ! $success ? ['id' => ['Product with given Product Option exists.']] : null,
                    additions: ['product_option' => $productOption]
                );
            }
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: ! $responses ? 204 : 207,
            data: $responses ?: null,
            statusMessage: ! $responses ? 'product option deleted' : 'not all product options could be deleted',
            bulk: (bool) $responses
        );
    }
}
