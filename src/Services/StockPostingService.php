<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\StockPosting;

class StockPostingService
{
    public function create(array $data): StockPosting
    {
        $data['stock'] = $this->getLatestStock(
            $data['warehouse_id'], $data['product_id'], $data['posting']
        );

        $stockPosting = new StockPosting($data);
        $stockPosting->save();

        return $stockPosting;
    }

    public function delete(string $id): array
    {
        $stockPosting = StockPosting::query()
            ->whereKey($id)
            ->first();

        if (! $stockPosting) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'stock posting not found']
            );
        }

        $stockPosting->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'stock posting deleted'
        );
    }

    /**
     * @return mixed
     */
    private function getLatestStock(int $warehouseId, int $productId, float $posting): float
    {
        $latestPosting = StockPosting::query()
            ->where('warehouse_id', '=', $warehouseId)
            ->where('product_id', '=', $productId)
            ->latest('id')
            ->first();

        if (empty($latestPosting->stock)) {
            return $posting;
        }

        return $latestPosting->stock + $posting;
    }
}
