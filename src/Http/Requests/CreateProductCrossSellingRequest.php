<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductCrossSellingRequest extends FormRequest
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
            'uuid' => 'string|uuid|unique:product_cross_sellings,uuid',
            'product_id' => 'required|integer|exists:products,id,deleted_at,NULL',
            'name' => 'required|string|max:255',
            'order_column' => 'integer',
            'is_active' => 'boolean',

            'products' => 'array',
            'products.*' => 'integer|exists:products,id,deleted_at,NULL',
        ];
    }
}
