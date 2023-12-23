<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateWarehouseRequest;
use FluxErp\Models\Warehouse;

class CreateWarehouse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateWarehouseRequest())->rules();
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function performAction(): Warehouse
    {
        $this->data['is_default'] = ! Warehouse::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            Warehouse::query()->update(['is_default' => false]);
        }

        $warehouse = new Warehouse($this->data);
        $warehouse->save();

        return $warehouse->fresh();
    }
}
