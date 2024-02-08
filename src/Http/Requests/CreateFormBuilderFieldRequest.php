<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\FormBuilderTypeEnum;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rule;

class CreateFormBuilderFieldRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'section_id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderSection::class),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => [
                'required',
                Rule::in(FormBuilderTypeEnum::values()),
            ],
            'ordering' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
        ];
    }
}
