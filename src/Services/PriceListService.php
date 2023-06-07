<?php

namespace FluxErp\Services;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdatePriceListRequest;
use FluxErp\Models\PriceList;

class PriceListService
{
    public function create(array $data): PriceList
    {
        $priceList = new PriceList($data);
        $priceList->save();

        return $priceList;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdatePriceListRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $priceList = PriceList::query()
                ->whereKey($item['id'])
                ->first();

            $priceList->fill($item);
            $priceList->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $priceList->withoutRelations(),
                additions: ['id' => $priceList->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'price-list(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $priceList = PriceList::query()
            ->whereKey($id)
            ->first();

        if (! $priceList) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'price-list not found']
            );
        }

        if ($priceList->prices()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['id' => 'price-list has associated prices']
            );
        }

        $priceList->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'price-list deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        if ($item['id'] ?? false) {
            $priceList = PriceList::query()
                ->whereKey($item['id'])
                ->first();

            // Check if new parent causes a cycle
            if ($item['parent_id'] ?? false) {
                if (Helper::checkCycle(PriceList::class, $priceList, $item['parent_id'])) {
                    return ResponseHelper::createArrayResponse(
                        statusCode: 409,
                        data: ['parent_id' => 'cycle detected'],
                        additions: $response
                    );
                }
            }
        }

        return null;
    }
}
