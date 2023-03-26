<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdatePriceRequest;
use FluxErp\Models\Price;

class PriceService
{
    public function create(array $data): Price
    {
        $price = new Price($data);
        $price->save();

        return $price;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdatePriceRequest()
        );

        foreach ($data as $item) {
            $price = Price::query()
                ->whereKey($item['id'])
                ->first();

            $price->fill($item);
            $price->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $price->withoutRelations(),
                additions: ['id' => $price->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'price(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $price = Price::query()
            ->whereKey($id)
            ->first();

        if (! $price) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'price not found']
            );
        }

        if ($price->orderPositions()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['id' => 'price has associated order-positions']
            );
        }

        $price->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'price deleted'
        );
    }
}
