<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateWarehouseRequest;
use FluxErp\Models\Warehouse;
use Illuminate\Support\Facades\Validator;

class CreateWarehouse implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateWarehouseRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'warehouse.create';
    }

    public static function description(): string|null
    {
        return 'create warehouse';
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function execute(): Warehouse
    {
        $warehouse = new Warehouse($this->data);
        $warehouse->save();

        return $warehouse;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
