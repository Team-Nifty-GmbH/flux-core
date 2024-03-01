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
        $this->data['is_default'] = ! app(Warehouse::class)->query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            app(Warehouse::class)->query()->update(['is_default' => false]);
        }

        $warehouse = app(Warehouse::class, ['attributes' => $this->data]);
        $warehouse->save();

        return $warehouse->fresh();
    }
}
