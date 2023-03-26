<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateProductOptionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:product_options,id,deleted_at,NULL',
            'product_option_group_id' => [
                'integer',
                (new ExistsWithIgnore('product_option_groups', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'sometimes|required|string',
        ];
    }
}
