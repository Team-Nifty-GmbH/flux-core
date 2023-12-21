<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateWarehouseRequest;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;

class UpdateWarehouse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateWarehouseRequest())->rules();
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function performAction(): Model
    {
        $this->data['is_default'] = ! Warehouse::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            Warehouse::query()->update(['is_default' => false]);
        }

        $warehouse = Warehouse::query()
            ->whereKey($this->data['id'])
            ->first();

        $warehouse->fill($this->data);
        $warehouse->save();

        return $warehouse->withoutRelations()->fresh();
    }
}
