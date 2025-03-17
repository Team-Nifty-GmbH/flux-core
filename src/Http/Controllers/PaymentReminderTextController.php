<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\PaymentReminderText\CreatePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\DeletePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\UpdatePaymentReminderText;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\PaymentReminderText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentReminderTextController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(PaymentReminderText::class);
    }

    public function create(Request $request): JsonResponse
    {
        $paymentReminderText = CreatePaymentReminderText::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $paymentReminderText,
            statusMessage: 'payment reminder text created'
        );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeletePaymentReminderText::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'payment reminder text deleted'
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
                    data: $paymentReminderText = UpdatePaymentReminderText::make($item)->validate()->execute(),
                    additions: ['id' => $paymentReminderText->id]
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
            'statusMessage' => $statusCode === 422 ? null : 'payment reminder text(s) updated',
        ]);
    }
}
