<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Lot;
use FluxErp\Models\StockPosting;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rulesets\StockPosting\UpdateStockPostingRuleset;
use Illuminate\Validation\ValidationException;

class UpdateStockPosting extends FluxAction
{
    public static function models(): array
    {
        return [StockPosting::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateStockPostingRuleset::class;
    }

    public function performAction(): StockPosting
    {
        $stockPosting = resolve_static(StockPosting::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $stockPosting->fill($this->data);
        $stockPosting->save();

        return $stockPosting->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void {}

    protected function validateData(): void
    {
        parent::validateData();

        $stockPosting = resolve_static(StockPosting::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $warehouseId = $this->data['warehouse_id'] ?? $stockPosting->warehouse_id;
        $productId = $this->data['product_id'] ?? $stockPosting->product_id;

        if (($this->data['warehouse_bin_id'] ?? false)
            && resolve_static(WarehouseBin::class, 'query')
                ->whereKey($this->data['warehouse_bin_id'])
                ->where('warehouse_id', '!=', $warehouseId)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'warehouse_bin_id' => ['The given warehouse bin belongs to a different warehouse'],
            ])->errorBag('updateStockPosting');
        }

        if (($this->data['lot_id'] ?? false)
            && resolve_static(Lot::class, 'query')
                ->whereKey($this->data['lot_id'])
                ->where('product_id', '!=', $productId)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'lot_id' => ['The given lot belongs to a different product'],
            ])->errorBag('updateStockPosting');
        }
    }
}
