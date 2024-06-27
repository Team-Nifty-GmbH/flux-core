<?php

namespace FluxErp\Rulesets\PriceList;

use FluxErp\Enums\RoundingMethodEnum;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class RoundingRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'rounding_method_enum' => [
                'string',
                'nullable',
                Rule::enum(RoundingMethodEnum::class),
            ],
            'rounding_precision' => 'required_unless:rounding_method_enum,none|integer|nullable',
            'rounding_number' => [
                'required_if:rounding_method_enum,nearest,end',
                'exclude_unless:rounding_method_enum,nearest,end',
                'integer',
                'nullable',
                'min:0',
            ],
            'rounding_mode' => [
                'required_if:rounding_method_enum,nearest,end',
                'exclude_unless:rounding_method_enum,nearest,end',
                'string',
                'nullable',
                'in:round,ceil,floor',
            ],
        ];
    }
}
