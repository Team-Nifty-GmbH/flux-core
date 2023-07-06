<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateOrderTypeRequest;
use FluxErp\Models\OrderType;
use Illuminate\Support\Facades\Validator;

class CreateOrderType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateOrderTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'order-type.create';
    }

    public static function description(): string|null
    {
        return 'create order type';
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function execute(): OrderType
    {
        $orderType = new OrderType($this->data);
        $orderType->save();

        return $orderType;
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
