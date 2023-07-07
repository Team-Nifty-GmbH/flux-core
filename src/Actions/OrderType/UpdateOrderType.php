<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateOrderTypeRequest;
use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateOrderType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateOrderTypeRequest())->rules();
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

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new OrderType());

        $this->data = $validator->validate();

        return $this;
    }
}
