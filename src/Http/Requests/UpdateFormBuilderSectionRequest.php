<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\FormBuilderSection;
use FluxErp\Rules\ModelExists;

class UpdateFormBuilderSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderSection::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'ordering' => 'nullable|integer|min:0',
            'columns' => 'nullable|integer|min:1|max:12',
        ];
    }
}
