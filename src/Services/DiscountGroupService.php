<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateDiscountGroupRequest;
use FluxErp\Models\DiscountGroup;

class DiscountGroupService
{
    public function create(array $data): DiscountGroup
    {
        $discountGroup = new DiscountGroup($data);
        $discountGroup->save();

        return $discountGroup;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateDiscountGroupRequest()
        );

        foreach ($data as $item) {
            $discountGroup = DiscountGroup::query()
                ->whereKey($item['id'])
                ->first();

            $discountGroup->fill($item);
            $discountGroup->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $discountGroup->withoutRelations(),
                additions: ['id' => $discountGroup->id]
            );
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
        $discountGroup = DiscountGroup::query()
            ->whereKey($id)
            ->first();

        if (! $discountGroup) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'discount group not found']
            );
        }

        $discountGroup->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'discount group deleted'
        );
    }
}
