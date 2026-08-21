<?php

namespace FluxErp\Actions\WarehouseBin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rulesets\WarehouseBin\UpdateWarehouseBinRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateWarehouseBin extends FluxAction
{
    public static function models(): array
    {
        return [WarehouseBin::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateWarehouseBinRuleset::class;
    }

    public function performAction(): Model
    {
        $warehouseBin = resolve_static(WarehouseBin::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $warehouseBin->fill($this->data);
        $warehouseBin->save();

        return $warehouseBin->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $warehouseBin = resolve_static(WarehouseBin::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if (resolve_static(WarehouseBin::class, 'query')
            ->whereKeyNot($warehouseBin->getKey())
            ->where('warehouse_id', $this->data['warehouse_id'] ?? $warehouseBin->warehouse_id)
            ->where('code', $this->data['code'] ?? $warehouseBin->code)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'code' => ['The given code is already taken in this warehouse'],
            ])->errorBag('updateWarehouseBin');
        }
    }
}
