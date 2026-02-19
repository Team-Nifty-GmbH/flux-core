<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Enums\ContactOptionTypeEnum;
use FluxErp\Models\ContactOption;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ContactOptionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'contact_options' => 'array',
            'contact_options.*.id' => [
                'integer',
                app(ModelExists::class, ['model' => ContactOption::class]),
            ],
            'contact_options.*.type' => [
                'required',
                app(EnumRule::class, ['type' => ContactOptionTypeEnum::class]),
            ],
            'contact_options.*.label' => 'required|string|max:255',
            'contact_options.*.value' => 'required|string|max:255',
            'contact_options.*.is_primary' => 'boolean',
        ];
    }
}
