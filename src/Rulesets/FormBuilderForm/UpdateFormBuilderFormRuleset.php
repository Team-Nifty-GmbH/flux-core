<?php

namespace FluxErp\Rulesets\FormBuilderForm;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateFormBuilderFormRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderForm::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderForm::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'start_date' => 'present|nullable|datetime:Y-m-d H:i:s',
            'end_date' => 'present|nullable|after:start_date|datetime:Y-m-d H:i:s',
            'is_active' => 'boolean',
        ];
    }
}
