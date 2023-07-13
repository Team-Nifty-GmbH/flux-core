<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateOrderTypeRequest;
use FluxErp\Models\OrderType;
use Illuminate\Support\Facades\Validator;

class CreateOrderType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateOrderTypeRequest())->rules();
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

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new OrderType());

        $this->data = $validator->validate();

        return $this;
    }
}
