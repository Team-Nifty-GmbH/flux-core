<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Warehouse;
use FluxErp\Rulesets\Warehouse\CreateWarehouseRuleset;

class CreateWarehouse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateWarehouseRuleset::class, 'getRules');
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
