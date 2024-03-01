<?php

namespace FluxErp\Services;

use FluxErp\Actions\AdditionalColumn\CreateAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\DeleteAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\UpdateAdditionalColumn;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;

class AdditionalColumnService
{
    public function create(array $data): array
    {
        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: CreateAdditionalColumn::make($data)->validate()->execute(),
            statusMessage: 'additional column created'
        );
    }

    public function update(array $data): array
    {
        try {
            $additionalColumn = UpdateAdditionalColumn::make($data)->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $additionalColumn,
            statusMessage: 'additional column updated'
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteAdditionalColumn::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'additional column deleted'
        );
    }
}
