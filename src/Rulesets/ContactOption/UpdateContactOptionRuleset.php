<?php

namespace FluxErp\Rulesets\ContactOption;

use FluxErp\Models\ContactOption;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Setting;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateContactOptionRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ContactOption::class),
            ],
            'type' => [
                'sometimes',
                'required',
                'string',
                Rule::in(
                    app(Setting::class)->query()
                        ->where('key', 'contact-options.types')
                        ->first()
                        ?->toArray()['settings'] ?: ['phone', 'email', 'website']
                ),
            ],
            'label' => 'sometimes|required|string',
            'value' => 'sometimes|required|string',
        ];
    }
}
