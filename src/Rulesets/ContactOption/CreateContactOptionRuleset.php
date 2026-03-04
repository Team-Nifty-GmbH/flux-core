<?php

namespace FluxErp\Rulesets\ContactOption;

use FluxErp\Enums\ContactOptionTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\ContactOption;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateContactOptionRuleset extends FluxRuleset
{
    protected static ?string $model = ContactOption::class;

    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'type' => [
                'required',
                app(EnumRule::class, ['type' => ContactOptionTypeEnum::class]),
            ],
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ];
    }
}
