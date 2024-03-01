<?php

namespace FluxErp\Services;

use FluxErp\Actions\Unit\CreateUnit;
use FluxErp\Actions\Unit\DeleteUnit;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Unit;
use Illuminate\Validation\ValidationException;

class UnitService
{
    public function create(array $data): Unit
    {
        return CreateUnit::make($data)->validate()->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeleteUnit::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'unit deleted'
        );
    }
}
