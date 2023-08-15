<?php

namespace FluxErp\Http\Requests;

class CreatePriceListRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|integer|exists:price_lists,id,deleted_at,NULL',
            'name' => 'required|string',
            'price_list_code' => 'required|string|unique:price_lists,price_list_code',
            'is_net' => 'required|boolean',
            'is_default' => 'boolean',

            'discount' => 'exclude_without:parent_id|exclude_if:parent_id,NULL|array',
            'discount.discount' => 'numeric|nullable',
            'discount.is_percentage' => 'required_with:discount.discount|boolean',
        ];
    }
}
