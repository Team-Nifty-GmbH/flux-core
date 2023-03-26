<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\AdditionalColumn;

class AdditionalColumnService
{
    public function create(array $data): array
    {
        if (! ($data['validations'] ?? false)) {
            $data['validations'] = null;
        }

        if (! ($data['values'] ?? false)) {
            $data['values'] = null;
        }

        $additionalColumn = new AdditionalColumn($data);
        $additionalColumn->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $additionalColumn,
            statusMessage: 'additional column created'
        );
    }

    public function update(array $data): array
    {
        if ($data['values'] ?? false) {
            $data['validations'] = null;
        } elseif (array_key_exists('values', $data)) {
            $data['values'] = null;
        }

        if ($data['validations'] ?? false) {
            $data['values'] = null;
        } elseif (array_key_exists('validations', $data)) {
            $data['validations'] = null;
        }

        $additionalColumn = AdditionalColumn::query()
            ->whereKey($data['id'])
            ->first();

        if ($additionalColumn->values !== null && $data['values'] !== null) {
            if ($additionalColumn->modelValues()->whereNotIn('meta.value', $data['values'])->exists()) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 409,
                    data: ['values' => 'Models with differing values exist']
                );
            }
        }

        $additionalColumn->fill($data);
        $additionalColumn->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $additionalColumn,
            statusMessage: 'additional column updated'
        );
    }

    public function delete(string $id): array
    {
        $additionalColumn = AdditionalColumn::query()
            ->whereKey($id)
            ->first();

        if (! $additionalColumn) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'additional column not found']
            );
        }

        $additionalColumn->modelValues()->delete();
        $additionalColumn->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'additional column deleted'
        );
    }
}
