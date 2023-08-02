<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateOrderPositionRequest;
use FluxErp\Http\Requests\FillOrderPositionRequest;
use FluxErp\Models\OrderPosition;
use FluxErp\Services\OrderPositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderPositionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderPosition();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, OrderPositionService $orderPositionService): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make(
            $data,
            array_merge(
                (new CreateOrderPositionRequest())->rules(),
                [
                    'price_id' => [
                        Rule::requiredIf(
                            ($data['is_free_text'] ?? false) === false &&
                            ($data['product_id'] ?? $data['price_list_id'] ?? false)
                        ),
                        'integer',
                        'exists:prices,id,deleted_at,NULL',
                        'exclude_if:is_free_text,true',
                    ],
                    'price_list_id' => [
                        Rule::requiredIf(
                            ($data['is_free_text'] ?? false) === false && ($data['price_id'] ?? false)
                        ),
                        'integer',
                        'exists:price_lists,id,deleted_at,NULL',
                        'exclude_if:is_free_text,true',
                    ],
                    'purchase_price' => [
                        Rule::requiredIf(
                            ($data['is_free_text'] ?? false) === false && ($data['product_id'] ?? false)
                        ),
                        'numeric',
                        'exclude_if:is_free_text,true',
                    ],
                    'unit_price' => [
                        Rule::requiredIf(
                            ($data['is_free_text'] ?? false) === false && ($data['price_id'] ?? false)
                        ),
                        'numeric',
                        'exclude_if:is_free_text,true',
                    ],
                    'vat_rate_percentage' => [
                        Rule::requiredIf(
                            ($data['is_free_text'] ?? false) === false && ($data['vat_rate_id'] ?? false)
                        ),
                        'numeric',
                        'exclude_if:is_free_text,true',
                    ],
                    'product_number' => [
                        Rule::requiredIf(
                            ($data['is_free_text'] ?? false) === false && ($data['product_id'] ?? false)
                        ),
                        'string',
                        'nullable',
                        'exclude_if:is_free_text,true',
                    ],
                ]
            )
        );
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $orderPosition = $orderPositionService->create($validator->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $orderPosition,
            statusMessage: 'order-position created'
        );
    }

    public function update(Request $request, OrderPositionService $orderPositionService): JsonResponse
    {
        $response = $orderPositionService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, OrderPositionService $orderPositionService): JsonResponse
    {
        $response = $orderPositionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function fill(FillOrderPositionRequest $request, OrderPositionService $orderPositionService): JsonResponse
    {
        $validated = $request->validated();

        $response = $orderPositionService->fill(
            $validated['order_id'],
            $validated['order_positions'],
            $validated['simulate'] ?? false
        );

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
