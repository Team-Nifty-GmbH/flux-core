<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdatePriceListRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:price_lists,id,deleted_at,NULL',
            'parent_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('price_lists', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'sometimes|required|string',
            'price_list_code' => 'sometimes|required|string',
            'is_net' => 'sometimes|boolean',
            'is_default' => 'boolean',

            'discount' => 'exclude_without:parent_id|exclude_if:parent_id,NULL|array',
            'discount.discount' => 'present|numeric|nullable',
            'discount.is_percentage' => 'boolean',
        ];
    }
}
