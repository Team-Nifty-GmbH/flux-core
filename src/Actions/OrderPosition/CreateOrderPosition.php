<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateOrderPositionRequest;
use FluxErp\Models\OrderPosition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateOrderPosition implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateOrderPositionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'order-position.create';
    }

    public static function description(): string|null
    {
        return 'create order position';
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function execute(): OrderPosition
    {
        $tags = Arr::pull($this->data, 'tags', []);

        $orderPosition = new OrderPosition($this->data);
        PriceCalculation::fill($orderPosition, $this->data);
        unset($orderPosition->discounts);
        $orderPosition->save();

        $orderPosition->attachTags($tags);

        return $orderPosition;
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
