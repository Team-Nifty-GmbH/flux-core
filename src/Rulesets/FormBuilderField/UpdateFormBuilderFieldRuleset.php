<?php

namespace FluxErp\Rulesets\FormBuilderField;

use FluxErp\Enums\FormBuilderTypeEnum;
use FluxErp\Models\FormBuilderField;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateFormBuilderFieldRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderField::class;

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
                Rule::enum(FormBuilderTypeEnum::class),
            ],
            'ordering' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
        ];
    }
}
