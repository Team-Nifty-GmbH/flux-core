<?php

namespace FluxErp\Http\Requests;

class CreateProjectCategoryTemplateRequest extends BaseFormRequest
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
            'categories' => 'sometimes|array',
        ];
    }
}
