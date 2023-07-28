<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Warehouse;
use Illuminate\Validation\ValidationException;

class DeleteWarehouse extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:warehouses,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function execute(): ?bool
    {
        return Warehouse::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

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

        return $this;
    }
}
