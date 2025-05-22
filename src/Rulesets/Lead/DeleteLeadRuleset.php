<?php

namespace FluxErp\Rulesets\Lead;

use FluxErp\Models\Lead;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLeadRuleset extends FluxRuleset
{
    protected static ?string $model = Lead::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Lead::class),
            ],
        ];
    }
}
