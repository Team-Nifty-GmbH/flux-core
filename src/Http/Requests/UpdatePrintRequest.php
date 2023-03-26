<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ViewExists;

class UpdatePrintRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'integer|required|exists:print_data,id',
            'model_type' => 'string|required_with:model_id|nullable',
            'model_id' => 'integer|required_with:model_type|nullable',
            'data' => 'sometimes|nullable',
            'view' => ['sometimes', 'string', new ViewExists()],
            'is_public' => 'sometimes|boolean',
            'is_template' => 'sometimes|boolean',
            'template_name' => 'string|required_if:is_template,true|nullable',
            'sort' => 'integer|min:1',
        ];
    }
}
