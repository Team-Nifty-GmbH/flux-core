<?php

namespace FluxErp\Rulesets\SerialNumberRange;

use FluxErp\Models\Client;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\HasSerialNumberRange;

class CreateSerialNumberRangeRuleset extends FluxRuleset
{
    protected static ?string $model = SerialNumberRange::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:serial_number_ranges,uuid',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'model_type' => [
                'required',
                'string',
                new MorphClassExists(HasSerialNumberRange::class),
            ],
            'model_id' => [
                'sometimes',
                'integer',
                new MorphExists(),
            ],
            'type' => 'required|string',
            'start_number' => 'integer|min:1',
            'prefix' => 'string|nullable',
            'suffix' => 'string|nullable',
            'description' => 'string|nullable',
            'length' => 'integer|min:1',
            'is_pre_filled' => 'boolean',
            'stores_serial_numbers' => 'boolean',
        ];
    }
}
