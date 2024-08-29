<?php

namespace FluxErp\Rulesets\ContactOrigin;

use FluxErp\Models\ContactOrigin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateContactOriginRuleset extends FluxRuleset
{
    protected static ?string $model = ContactOrigin::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ContactOrigin::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
