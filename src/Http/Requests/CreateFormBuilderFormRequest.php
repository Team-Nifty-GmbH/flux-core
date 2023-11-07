<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormBuilderFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'ordering' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'details' => 'nullable|array',
            'options' => 'nullable|array',
            'extensions' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }
}
