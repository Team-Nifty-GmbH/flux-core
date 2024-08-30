<?php

namespace FluxErp\Rulesets\ContactOrigin;

use FluxErp\Models\ContactOrigin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteContactOriginRuleset extends FluxRuleset
{
    protected static ?string $model = ContactOrigin::class;

    protected static bool $addAdditionalColumnRules = false;

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
