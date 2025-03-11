<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\PaymentReminder\DeletePaymentReminder;
use FluxErp\Actions\PaymentReminder\UpdatePaymentReminder;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\PaymentReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentReminderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(PaymentReminder::class);
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $paymentReminder = CreatePaymentReminder::make($request->all())
                ->validate()
                ->execute();

            $response = ResponseHelper::createArrayResponse(
                statusCode: 201,
                data: $paymentReminder,
                statusMessage: 'payment reminder created'
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
            DeletePaymentReminder::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'payment reminder deleted'
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
        try {
            $paymentReminder = UpdatePaymentReminder::make($request->all())
                ->validate()
                ->execute();

            $response = ResponseHelper::createArrayResponse(
                statusCode: 201,
                data: $paymentReminder,
                statusMessage: 'payment reminder updated'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
