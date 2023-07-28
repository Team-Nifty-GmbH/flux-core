<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Warehouse;
use Illuminate\Validation\ValidationException;

class DeleteWarehouse extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:warehouses,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function performAction(): ?bool
    {
        return Warehouse::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if (Warehouse::query()
            ->whereKey($this->data['id'])
            ->first()
            ->children()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'children' => [__('The given warehouse has children')],
            ])->errorBag('deleteWarehouse');
        }
    }
}
