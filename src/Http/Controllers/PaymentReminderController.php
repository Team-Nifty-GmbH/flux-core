<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\PaymentReminder\DeletePaymentReminder;
use FluxErp\Actions\PaymentReminder\UpdatePaymentReminder;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreatePaymentReminderRequest;
use FluxErp\Http\Requests\UpdatePaymentReminderRequest;
use FluxErp\Models\PaymentReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PaymentReminderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new PaymentReminder();
    }

    public function create(CreatePaymentReminderRequest $request): JsonResponse
    {
        try {
            $paymentReminder = CreatePaymentReminder::make($request->validated())
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

    public function update(UpdatePaymentReminderRequest $request): JsonResponse
    {
        try {
            $paymentReminder = UpdatePaymentReminder::make($request->validated())
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
}
