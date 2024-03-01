<?php

namespace FluxErp\Rulesets\FormBuilderField;

use FluxErp\Enums\FormBuilderTypeEnum;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateFormBuilderFieldRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderField::class;

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
                Rule::enum(FormBuilderTypeEnum::class),
            ],
            'ordering' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
        ];
    }
}
