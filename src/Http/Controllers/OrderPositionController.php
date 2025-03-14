<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\FillOrderPositions;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\OrderPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderPositionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(OrderPosition::class);
    }

    public function create(Request $request): JsonResponse
    {
        $orderPosition = CreateOrderPosition::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $orderPosition,
            statusMessage: 'order-position created'
        );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteOrderPosition::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'order position deleted'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function fill(Request $request): JsonResponse
    {
        try {
            $orderPositions = FillOrderPositions::make($request->all())
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $e->errors(),
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $orderPositions
        );
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
                    data: $orderPosition = UpdateOrderPosition::make($item)->validate()->execute(),
                    additions: ['id' => $orderPosition->id]
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
            'statusMessage' => $statusCode === 422 ? null : 'order position(s) updated',
        ]);
    }
}
