<?php

namespace FluxErp\Rulesets\FormBuilderResponse;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateFormBuilderResponseRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderResponse::class;

    public function rules(): array
    {
        return [
            'form_id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderForm::class),
            ],
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
        ];
    }
}
