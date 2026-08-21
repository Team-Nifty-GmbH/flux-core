<?php

namespace FluxErp\Actions\WarehouseBin;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
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

        $warehouseId = $this->data['warehouse_id'] ?? $warehouseBin->warehouse_id;
        $parentId = $this->data['parent_id'] ?? $warehouseBin->parent_id;

        if ($parentId
            && resolve_static(WarehouseBin::class, 'query')
                ->whereKey($parentId)
                ->where('warehouse_id', '!=', $warehouseId)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'parent_id' => ['The parent bin belongs to a different warehouse'],
            ])->errorBag('updateWarehouseBin');
        }

        if (($this->data['parent_id'] ?? false)
            && Helper::checkCycle(WarehouseBin::class, $warehouseBin, $this->data['parent_id'])
        ) {
            throw ValidationException::withMessages([
                'parent_id' => ['Cycle detected'],
            ])->errorBag('updateWarehouseBin');
        }

        if ((int) $warehouseId !== (int) $warehouseBin->warehouse_id && $warehouseBin->getAllDescendantsQuery()->exists()) {
            throw ValidationException::withMessages([
                'warehouse_id' => ['The given warehouse bin has child bins in its current warehouse'],
            ])->errorBag('updateWarehouseBin');
        }

        if (resolve_static(WarehouseBin::class, 'query')
            ->withTrashed()
            ->whereKeyNot($warehouseBin->getKey())
            ->where('warehouse_id', $warehouseId)
            ->where('code', $this->data['code'] ?? $warehouseBin->code)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'code' => ['The given code is already taken in this warehouse'],
            ])->errorBag('updateWarehouseBin');
        }
    }
}
