<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormBuilderResponseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'form_id' => 'required|exists:form_builder_forms,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
