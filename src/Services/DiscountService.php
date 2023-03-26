<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateDiscountRequest;
use FluxErp\Models\Discount;

class DiscountService
{
    public function create(array $data): Discount
    {
        $discount = new Discount($data);
        $discount->save();

        return $discount;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateDiscountRequest()
        );

        foreach ($data as $item) {
            $discount = Discount::query()
                ->whereKey($item['id'])
                ->first();

            $discount->fill($item);
            $discount->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $discount->withoutRelations(),
                additions: ['id' => $discount->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'discount(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $discount = Discount::query()
            ->whereKey($id)
            ->first();

        if (! $discount) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'discount not found']
            );
        }

        $discount->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'discount deleted'
        );
    }
}
