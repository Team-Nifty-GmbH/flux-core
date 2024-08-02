<?php

namespace FluxErp\Rulesets\SerialNumberRange;

use FluxErp\Models\Client;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
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
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class, ['model' => HasSerialNumberRange::class]),
            ],
            'model_id' => [
                'sometimes',
                'integer',
                app(MorphExists::class),
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
