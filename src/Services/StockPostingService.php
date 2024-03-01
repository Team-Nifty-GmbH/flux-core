<?php

namespace FluxErp\Services;

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Actions\StockPosting\DeleteStockPosting;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\StockPosting;
use Illuminate\Validation\ValidationException;

class StockPostingService
{
    public function create(array $data): StockPosting
    {
        return CreateStockPosting::make($data)->validate()->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeleteStockPosting::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'stock posting deleted'
        );
    }
}
