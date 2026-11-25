<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Models\Warehouse;
use FluxErp\Rulesets\Warehouse\CreateWarehouseRuleset;

class CreateWarehouse extends FluxAction
{
    public static function models(): array
    {
        return [Warehouse::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateWarehouseRuleset::class;
    }

    public function performAction(): Warehouse
    {
        $warehouse = app(Warehouse::class, ['attributes' => $this->data]);
        $warehouse->save();

        return $warehouse->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['stock_removal_strategy_enum'] ??= StockRemovalStrategyEnum::Fifo->value;
    }
}
