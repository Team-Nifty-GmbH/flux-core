<?php

namespace FluxErp\Rulesets\FormBuilderFieldResponse;

use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateFormBuilderFieldResponseRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderFieldResponse::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderFieldResponse::class]),
            ],
            'response' => 'required|string',
        ];
    }
}
