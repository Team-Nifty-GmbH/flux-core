<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Warehouse;
use Illuminate\Validation\ValidationException;

class DeleteWarehouse extends FluxAction
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
            ->stockPostings()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'stock_postings' => [__('The given warehouse has stock postings')],
            ])->errorBag('deleteWarehouse');
        }
    }
}
