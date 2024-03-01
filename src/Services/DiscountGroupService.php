<?php

namespace FluxErp\Services;

use FluxErp\Actions\DiscountGroup\CreateDiscountGroup;
use FluxErp\Actions\DiscountGroup\DeleteDiscountGroup;
use FluxErp\Actions\DiscountGroup\UpdateDiscountGroup;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\DiscountGroup;
use Illuminate\Validation\ValidationException;

class DiscountGroupService
{
    public function create(array $data): DiscountGroup
    {
        return CreateDiscountGroup::make($data)->validate()->execute();
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
                    data: $discountGroup = UpdateDiscountGroup::make($item)->validate()->execute(),
                    additions: ['id' => $discountGroup->id]
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
            statusMessage: $statusCode === 422 ? null : 'discount group(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteDiscountGroup::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'discount group deleted'
        );
    }
}
