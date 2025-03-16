<?php

namespace FluxErp\Rulesets\ContactOrigin;

use FluxErp\Models\ContactOrigin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteContactOriginRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = ContactOrigin::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ContactOrigin::class]),
            ],
        ];
    }
}
