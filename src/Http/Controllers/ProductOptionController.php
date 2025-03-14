<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\ProductOption\CreateProductOption;
use FluxErp\Actions\ProductOption\DeleteProductOption;
use FluxErp\Actions\ProductOption\UpdateProductOption;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\ProductOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductOptionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(ProductOption::class);
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $productOption = CreateProductOption::make($request->all())->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $productOption,
            statusMessage: 'product option created'
        );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteProductOption::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 204,
            statusMessage: 'product option deleted'
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
                    data: $productOption = UpdateProductOption::make($item)->validate()->execute(),
                    additions: ['id' => $productOption->id]
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
                    ['statusMessage' => 'product option updated']
                )
            ) :
            ResponseHelper::createResponseFromBase(
                statusCode: $statusCode,
                data: $responses,
                statusMessage: $statusCode === 422 ? null : 'product option(s) updated',
                bulk: true
            );
    }
}
