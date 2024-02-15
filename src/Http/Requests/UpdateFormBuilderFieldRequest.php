<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\FormBuilderTypeEnum;
use FluxErp\Models\FormBuilderField;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rule;

class UpdateFormBuilderFieldRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderField::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => [
                'sometimes',
                'required',
                Rule::in(FormBuilderTypeEnum::values()),
            ],
            'ordering' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
        ];
    }
}
