<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdatePriceRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:prices,id,deleted_at,NULL',
            'product_id' => [
                'integer',
                (new ExistsWithIgnore('products', 'id'))->whereNull('deleted_at'),
            ],
            'price_list_id' => [
                'integer',
                (new ExistsWithIgnore('price_lists', 'id'))->whereNull('deleted_at'),
            ],
            'price' => 'sometimes|numeric',
        ];
    }
}
