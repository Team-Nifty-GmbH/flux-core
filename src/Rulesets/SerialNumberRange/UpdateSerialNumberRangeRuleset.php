<?php

namespace FluxErp\Rulesets\SerialNumberRange;

use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateSerialNumberRangeRuleset extends FluxRuleset
{
    protected static ?string $model = SerialNumberRange::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(SerialNumberRange::class),
            ],
            'type' => 'sometimes|required|string',
            'current_number' => 'integer|min:1',
            'prefix' => 'string|nullable',
            'suffix' => 'string|nullable',
            'description' => 'string|nullable',
            'length' => 'integer|min:1',
            'is_pre_filled' => 'boolean',
            'stores_serial_numbers' => 'boolean',
        ];
    }
}
