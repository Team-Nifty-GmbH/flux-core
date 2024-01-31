<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Payment\CreatePaymentRun;
use FluxErp\Actions\Payment\DeletePaymentRun;
use FluxErp\Actions\Payment\UpdatePaymentRun;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreatePaymentRunRequest;
use FluxErp\Models\PaymentRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentRunController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new PaymentRun();
    }

    public function create(CreatePaymentRunRequest $request): JsonResponse
    {
        $paymentRun = CreatePaymentRun::make($request->validated())
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $paymentRun,
            statusMessage: 'payment run created'
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
                    data: $paymentRun = UpdatePaymentRun::make($item)->validate()->execute(),
                    additions: ['id' => $paymentRun->id]
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
            'statusMessage' => $statusCode === 422 ? null : 'payment run(s) updated',
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeletePaymentRun::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'payment run deleted'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
