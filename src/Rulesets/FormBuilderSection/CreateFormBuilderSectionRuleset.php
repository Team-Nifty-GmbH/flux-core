<?php

namespace FluxErp\Rulesets\FormBuilderSection;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateFormBuilderSectionRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderSection::class;

    public function rules(): array
    {
        return [
            'form_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderForm::class]),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ordering' => 'nullable|integer|min:0',
            'columns' => 'nullable|integer|min:1|max:12',
        ];
    }
}
