<?php

namespace FluxErp\Rulesets\SerialNumberRange;

use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\Tenant;
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
            'uuid' => 'nullable|string|uuid|unique:serial_number_ranges,uuid',
            'tenant_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
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
            'type' => 'required|string|max:255',
            'start_number' => 'integer|min:1',
            'prefix' => 'string|max:255|nullable',
            'suffix' => 'string|max:255|nullable',
            'description' => 'string|nullable',
            'length' => 'integer|min:1',
            'is_pre_filled' => 'boolean',
            'stores_serial_numbers' => 'boolean',
        ];
    }
}
