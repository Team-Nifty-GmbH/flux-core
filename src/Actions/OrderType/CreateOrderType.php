<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\OrderType\CreateOrderTypeRuleset;
use Illuminate\Support\Facades\Validator;

class CreateOrderType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateOrderTypeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function performAction(): OrderType
    {
        $orderType = app(OrderType::class, ['attributes' => $this->data]);
        $orderType->save();

        return $orderType->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(OrderType::class));

        $this->data = $validator->validate();
    }
}
