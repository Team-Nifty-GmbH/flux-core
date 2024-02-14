<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\DeletePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\UpdatePurchaseInvoicePosition;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreatePurchaseInvoicePositionRequest;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseInvoicePositionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new PurchaseInvoice();
    }

    public function create(CreatePurchaseInvoicePositionRequest $request): JsonResponse
    {
        $purchaseInvoicePosition = CreatePurchaseInvoicePosition::make($request->validated())
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $purchaseInvoicePosition,
            statusMessage: 'purchase invoice position created'
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
                    data: $purchaseInvoicePosition = UpdatePurchaseInvoicePosition::make($item)->validate()->execute(),
                    additions: ['id' => $purchaseInvoicePosition->id]
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
            'statusMessage' => $statusCode === 422 ? null : 'purchase invoice position(s) updated',
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeletePurchaseInvoicePosition::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'purchase invoice position deleted'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
