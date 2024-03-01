<?php

namespace FluxErp\Rulesets\FormBuilderSection;

use FluxErp\Models\FormBuilderSection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateFormBuilderSectionRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderSection::class;

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
