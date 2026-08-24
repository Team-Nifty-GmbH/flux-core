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
    private ?WarehouseBin $warehouseBin = null;

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
        $this->warehouseBin->fill($this->getData());
        $this->warehouseBin->save();

        return $this->warehouseBin->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->warehouseBin = resolve_static(WarehouseBin::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $warehouseId = $this->getData('warehouse_id', $this->warehouseBin->warehouse_id);
        $parentId = $this->getData('parent_id', $this->warehouseBin->parent_id);

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

        if (($this->getData('parent_id', false))
            && Helper::checkCycle(WarehouseBin::class, $this->warehouseBin, $this->getData('parent_id'))
        ) {
            throw ValidationException::withMessages([
                'parent_id' => ['Cycle detected'],
            ])->errorBag('updateWarehouseBin');
        }

        if ((int) $warehouseId !== (int) $this->warehouseBin->warehouse_id && $this->warehouseBin->getAllDescendantsQuery()->exists()) {
            throw ValidationException::withMessages([
                'warehouse_id' => ['The given warehouse bin has child bins in its current warehouse'],
            ])->errorBag('updateWarehouseBin');
        }

        if (resolve_static(WarehouseBin::class, 'query')
            ->whereKeyNot($this->warehouseBin->getKey())
            ->where('warehouse_id', $warehouseId)
            ->where('code', $this->getData('code', $this->warehouseBin->code))
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'code' => ['The given code is already taken in this warehouse'],
            ])->errorBag('updateWarehouseBin');
        }
    }
}
