<?php

namespace FluxErp\Rulesets\FormBuilderResponse;

use FluxErp\Models\FormBuilderResponse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFormBuilderResponseRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderResponse::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderResponse::class),
            ],
        ];
    }
}
