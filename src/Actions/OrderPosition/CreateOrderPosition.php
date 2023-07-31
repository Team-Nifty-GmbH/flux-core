<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateOrderPositionRequest;
use FluxErp\Models\OrderPosition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateOrderPosition extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = array_merge(
            (new CreateOrderPositionRequest())->rules(),
            [
                'price_id' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false &&
                        ($this->data['product_id'] ?? $this->data['price_list_id'] ?? false)
                    ),
                    'integer',
                    'exists:prices,id,deleted_at,NULL',
                    'exclude_if:is_free_text,true',
                ],
                'price_list_id' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['price_id'] ?? false)
                    ),
                    'integer',
                    'exists:price_lists,id,deleted_at,NULL',
                    'exclude_if:is_free_text,true',
                ],
                'purchase_price' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['product_id'] ?? false)
                    ),
                    'numeric',
                    'exclude_if:is_free_text,true',
                ],
                'unit_price' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['price_id'] ?? false)
                    ),
                    'numeric',
                    'exclude_if:is_free_text,true',
                ],
                'vat_rate' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['vat_rate_id'] ?? false)
                    ),
                    'numeric',
                    'exclude_if:is_free_text,true',
                ],
                'product_number' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['product_id'] ?? false)
                    ),
                    'string',
                    'nullable',
                    'exclude_if:is_free_text,true',
                ],
            ]
        );
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

        return $orderPosition->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new OrderPosition());

        $this->data = $validator->validate();

        return $this;
    }
}
