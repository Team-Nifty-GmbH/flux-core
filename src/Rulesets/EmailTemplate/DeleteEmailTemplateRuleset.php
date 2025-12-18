<?php

namespace FluxErp\Rulesets\EmailTemplate;

use FluxErp\Models\EmailTemplate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteEmailTemplateRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmailTemplate::class]),
            ],
        ];
    }
}
