<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Unit;

class UnitService
{
    public function create(array $data): Unit
    {
        $unit = new Unit($data);
        $unit->save();

        return $unit;
    }

    public function delete(string $id): array
    {
        $unit = Unit::query()
            ->whereKey($id)
            ->first();

        if (! $unit) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'unit not found']
            );
        }

        $unit->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'unit deleted'
        );
    }
}
