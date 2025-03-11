<?php

namespace FluxErp\Services;

use FluxErp\Actions\Price\CreatePrice;
use FluxErp\Actions\Price\DeletePrice;
use FluxErp\Actions\Price\UpdatePrice;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Price;
use Illuminate\Validation\ValidationException;

class PriceService
{
    public function create(array $data): Price
    {
        return CreatePrice::make($data)->validate()->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeletePrice::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'price deleted'
        );
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $price = UpdatePrice::make($item)->validate()->execute(),
                    additions: ['id' => $price->id]
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

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'price(s) updated',
            bulk: true
        );
    }
}
