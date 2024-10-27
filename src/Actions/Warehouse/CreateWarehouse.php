<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Warehouse;
use FluxErp\Rulesets\Warehouse\CreateWarehouseRuleset;

class CreateWarehouse extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreateWarehouseRuleset::class;
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function performAction(): Warehouse
    {
        $warehouse = app(Warehouse::class, ['attributes' => $this->data]);
        $warehouse->save();

        return $warehouse->fresh();
    }
}
