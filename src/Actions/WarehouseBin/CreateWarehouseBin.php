<?php

namespace FluxErp\Actions\WarehouseBin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rulesets\WarehouseBin\CreateWarehouseBinRuleset;
use Illuminate\Validation\ValidationException;

class CreateWarehouseBin extends FluxAction
{
    public static function models(): array
    {
        return [WarehouseBin::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateWarehouseBinRuleset::class;
    }

    public function performAction(): WarehouseBin
    {
        $warehouseBin = app(WarehouseBin::class, ['attributes' => $this->data]);
        $warehouseBin->save();

        return $warehouseBin->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $parentId = $this->data['parent_id'] ?? null;

        if ($parentId
            && resolve_static(WarehouseBin::class, 'query')
                ->whereKey($parentId)
                ->where('warehouse_id', '!=', $this->data['warehouse_id'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'parent_id' => ['The parent bin belongs to a different warehouse'],
            ])->errorBag('createWarehouseBin');
        }

        if (resolve_static(WarehouseBin::class, 'query')
            ->withTrashed()
            ->where('warehouse_id', $this->data['warehouse_id'])
            ->where('code', $this->data['code'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'code' => ['The given code is already taken in this warehouse'],
            ])->errorBag('createWarehouseBin');
        }
    }
}
