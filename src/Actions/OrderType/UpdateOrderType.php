<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\OrderType\UpdateOrderTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateOrderType extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateOrderTypeRuleset::class;
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function performAction(): Model
    {
        $orderType = resolve_static(OrderType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $orderType->fill($this->data);
        $orderType->save();

        return $orderType->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(OrderType::class));

        $this->data = $validator->validate();
    }
}
