<?php

namespace FluxErp\Rulesets\FormBuilderResponse;

use FluxErp\Models\FormBuilderResponse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFormBuilderResponseRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = FormBuilderResponse::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderResponse::class]),
            ],
        ];
    }
}
