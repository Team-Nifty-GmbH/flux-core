<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\DeletePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\UpdatePurchaseInvoice;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateOrderFromPurchaseInvoiceRequest;
use FluxErp\Http\Requests\CreatePurchaseInvoiceRequest;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseInvoiceController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new PurchaseInvoice();
    }

    public function create(CreatePurchaseInvoiceRequest $request): JsonResponse
    {
        try {
            $purchaseInvoice = CreatePurchaseInvoice::make($request->validated())
                ->validate()
                ->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 201,
                data: $purchaseInvoice,
                statusMessage: 'purchase invoice created'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: 422,
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
                    data: $purchaseInvoice = UpdatePurchaseInvoice::make($item)->validate()->execute(),
                    additions: ['id' => $purchaseInvoice->id]
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
            'statusMessage' => $statusCode === 422 ? null : 'purchase invoice(s) updated',
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeletePurchaseInvoice::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'purchase invoice deleted'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function finish(CreateOrderFromPurchaseInvoiceRequest $request): JsonResponse
    {
        $order = CreateOrderFromPurchaseInvoice::make($request->validated())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $order,
            statusMessage: 'order from purchase invoice created'
        );
    }
}
