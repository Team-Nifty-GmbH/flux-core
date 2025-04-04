<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Warehouse;
use FluxErp\Rulesets\Warehouse\UpdateWarehouseRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateWarehouse extends FluxAction
{
    public static function models(): array
    {
        return [Warehouse::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateWarehouseRuleset::class;
    }

    public function performAction(): Model
    {
        $warehouse = resolve_static(Warehouse::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $warehouse->fill($this->data);
        $warehouse->save();

        return $warehouse->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (($this->data['is_default'] ?? false)
            && ! resolve_static(Warehouse::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }
}
