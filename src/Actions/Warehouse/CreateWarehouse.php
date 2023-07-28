<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateWarehouseRequest;
use FluxErp\Models\Warehouse;

class CreateWarehouse extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateWarehouseRequest())->rules();
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function execute(): Warehouse
    {
        $warehouse = new Warehouse($this->data);
        $warehouse->save();

        return $warehouse->fresh();
    }
}
