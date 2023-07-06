<?php

namespace FluxErp\Services;

use FluxErp\Actions\Locking\LockModel;
use FluxErp\Actions\Locking\UnlockModel;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;

class LockService
{
    public function create(array $data, string $modelType): array
    {
        try {
            LockModel::make(array_merge($data, ['model_type' => $modelType]))->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            statusMessage: 'model locked'
        );
    }

    public function delete(int $id, string $modelType): array
    {
        try {
            UnlockModel::make(['id' => $id, 'model_type' => $modelType])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            statusMessage: 'model unlocked'
        );
    }
}
