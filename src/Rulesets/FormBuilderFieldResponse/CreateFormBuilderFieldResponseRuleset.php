<?php

namespace FluxErp\Rulesets\FormBuilderFieldResponse;

use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateFormBuilderFieldResponseRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderFieldResponse::class;

    public function rules(): array
    {
        return [
            'form_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderForm::class]),
            ],
            'field_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderField::class]),
            ],
            'response_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderResponse::class]),
            ],
            'response' => 'required|string',
        ];
    }
}
