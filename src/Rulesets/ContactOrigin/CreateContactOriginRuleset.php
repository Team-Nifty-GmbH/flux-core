<?php

namespace FluxErp\Rulesets\ContactOrigin;

use FluxErp\Models\ContactOrigin;
use FluxErp\Rulesets\FluxRuleset;

class CreateContactOriginRuleset extends FluxRuleset
{
    protected static ?string $model = ContactOrigin::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:contact_origins,uuid',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
