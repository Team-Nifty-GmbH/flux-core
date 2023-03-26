<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdatePaymentTypeRequest;
use FluxErp\Models\PaymentType;

class PaymentTypeService
{
    public function create(array $data): PaymentType
    {
        $paymentType = new PaymentType($data);
        $paymentType->save();

        return $paymentType;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdatePaymentTypeRequest(),
            model: new PaymentType()
        );

        foreach ($data as $item) {
            // Find existing data to update.
            $paymentType = PaymentType::query()
                ->whereKey($item['id'])
                ->first();

            // Save new data to table.
            $paymentType->fill($item);
            $paymentType->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $paymentType->withoutRelations()->fresh(),
                additions: ['id' => $paymentType->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'payment types updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $paymentType = PaymentType::query()
            ->whereKey($id)
            ->first();

        if (! $paymentType) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'payment type not found']
            );
        }

        $paymentType->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'payment type deleted'
        );
    }
}
