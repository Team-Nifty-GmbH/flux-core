<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateVatRateRequest;
use FluxErp\Models\VatRate;

class VatRateService
{
    public function create(array $data): VatRate
    {
        $vatRate = new VatRate($data);
        $vatRate->save();

        return $vatRate;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateVatRateRequest(),
        );

        foreach ($data as $item) {
            $vatRate = VatRate::query()
                ->whereKey($item['id'])
                ->first();

            $vatRate->fill($item);
            $vatRate->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $vatRate->withoutRelations()->fresh(),
                additions: ['id' => $vatRate->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'vat rates updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $vatRate = VatRate::query()
            ->whereKey($id)
            ->first();

        if (! $vatRate) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'vat rate not found']
            );
        }

        $vatRate->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'vat rate deleted'
        );
    }
}
