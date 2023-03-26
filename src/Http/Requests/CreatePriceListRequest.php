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
            'name' => 'required|string',
            'price_list_code' => 'required|string|unique:price_lists,price_list_code',
            'is_net' => 'required|boolean',
        ];
    }
}
