<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductCrossSellingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:product_cross_sellings,id',
            'product_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('products', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'sometimes|required|string|max:255',
            'order_column' => 'integer',
            'is_active' => 'boolean',

            'products' => 'array',
            'products.*' => 'integer|exists:products,id,deleted_at,NULL',
        ];
    }
}
