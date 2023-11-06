<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormBuilderResponseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_responses,id',
            'form_id' => 'required|exists:form_builder_forms,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
