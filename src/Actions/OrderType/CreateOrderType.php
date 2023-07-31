<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateOrderTypeRequest;
use FluxErp\Models\OrderType;
use Illuminate\Support\Facades\Validator;

class CreateOrderType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateOrderTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function performAction(): OrderType
    {
        $orderType = new OrderType($this->data);
        $orderType->save();

        return $orderType;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new OrderType());

        $this->data = $validator->validate();
    }
}
