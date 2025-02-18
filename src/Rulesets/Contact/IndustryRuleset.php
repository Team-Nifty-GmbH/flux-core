<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Models\Industry;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class IndustryRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'industries' => 'nullable|array',
            'industries.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Industry::class]),
            ],
        ];
    }
}
