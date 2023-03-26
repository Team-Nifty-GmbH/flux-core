<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Traits\Lockable;
use Illuminate\Support\Facades\Auth;

class LockService
{
    public function create(array $data, string $modelType): array
    {
        if (! in_array(Lockable::class, class_uses($modelType) ?: [])) {
            return ResponseHelper::createArrayResponse(
                statusCode: 405,
                data: ['lock' => 'model not lockable']
            );
        }

        if ($modelType::query()
            ->whereKey($data['id'])
            ->whereRelation('lock', 'created_by', '!=', Auth::id())
            ->exists()
        ) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['model is locked by another user']
            );
        }

        $model = $modelType::query()
            ->whereKey($data['id'])
            ->first();

        if (! $model->lock()->exists()) {
            $model->lock()->create();
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            statusMessage: 'model locked'
        );
    }

    public function delete(int $id, string $modelType): array
    {
        if (! in_array(Lockable::class, class_uses($modelType) ?: [])) {
            return ResponseHelper::createArrayResponse(
                statusCode: 405,
                data: ['lock' => 'model not lockable']
            );
        }

        $model = $modelType::query()
            ->whereKey($id)
            ->first();

        if (! $model) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'model not found']
            );
        }

        if ($model->lock && $model->lock->created_by !== Auth::id()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['lock' => 'model is locked by another user']
            );
        }

        $model->lock()->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            statusMessage: 'model unlocked'
        );
    }
}
