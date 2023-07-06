<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateOrderTypeRequest;
use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateOrderType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateOrderTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'order-type.update';
    }

    public static function description(): string|null
    {
        return 'update order type';
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function execute(): Model
    {
        $orderType = OrderType::query()
            ->whereKey($this->data['id'])
            ->first();

        $orderType->fill($this->data);
        $orderType->save();

        return $orderType->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new OrderType());

        $this->data = $validator->validate();

        return $this;
    }
}
