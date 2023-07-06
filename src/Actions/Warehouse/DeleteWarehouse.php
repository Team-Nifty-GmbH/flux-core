<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Warehouse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteWarehouse implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:warehouses,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'warehouse.delete';
    }

    public static function description(): string|null
    {
        return 'delete warehouse';
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function execute()
    {
        return Warehouse::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

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
