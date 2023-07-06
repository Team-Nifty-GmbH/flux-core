<?php

namespace FluxErp\Actions\Warehouse;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateWarehouseRequest;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateWarehouse implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateWarehouseRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'warehouse.update';
    }

    public static function description(): string|null
    {
        return 'update warehouse';
    }

    public static function models(): array
    {
        return [Warehouse::class];
    }

    public function execute(): Model
    {
        $warehouse = Warehouse::query()
            ->whereKey($this->data['id'])
            ->first();

        $warehouse->fill($this->data);
        $warehouse->save();

        return $warehouse->withoutRelations()->fresh();
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
