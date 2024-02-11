<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Rules\ModelExists;

class CreateFormBuilderSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'form_id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderForm::class),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ordering' => 'nullable|integer|min:0',
            'columns' => 'nullable|integer|min:1|max:12',
        ];
    }
}
