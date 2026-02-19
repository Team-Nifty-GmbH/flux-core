<?php

namespace FluxErp\Rulesets\ContactOption;

use FluxErp\Enums\ContactOptionTypeEnum;
use FluxErp\Models\ContactOption;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateContactOptionRuleset extends FluxRuleset
{
    protected static ?string $model = ContactOption::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ContactOption::class]),
            ],
            'type' => [
                'sometimes',
                'required',
                app(EnumRule::class, ['type' => ContactOptionTypeEnum::class]),
            ],
            'label' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|string|max:255',
        ];
    }
}
