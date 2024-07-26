<?php

namespace FluxErp\Rulesets\FormBuilderForm;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateFormBuilderFormRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderForm::class;

    public function rules(): array
    {
        return [
            'model_type' => [
                'required_with:model_id',
                'string',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                app(MorphExists::class),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'start_date' => 'present|nullable|datetime:Y-m-d H:i:s',
            'end_date' => 'present|nullable|after:start_date|datetime:Y-m-d H:i:s',
            'is_active' => 'boolean',
        ];
    }
}
