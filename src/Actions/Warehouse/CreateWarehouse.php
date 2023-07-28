<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateWarehouseRequest;
use FluxErp\Models\Warehouse;

class CreateWarehouse extends BaseAction
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
        $warehouse = new Warehouse($this->data);
        $warehouse->save();

        return $warehouse;
    }
}
