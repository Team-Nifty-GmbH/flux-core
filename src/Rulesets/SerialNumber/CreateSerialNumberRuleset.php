<?php

namespace FluxErp\Rulesets\SerialNumber;

use FluxErp\Models\Address;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateSerialNumberRuleset extends FluxRuleset
{
    protected static ?string $model = SerialNumber::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:serial_numbers,uuid',
            'serial_number_range_id' => [
                'exclude_if:use_supplier_serial_number,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => SerialNumberRange::class]),
            ],
            'serial_number' => 'required|string|unique:serial_numbers,serial_number',
            'supplier_serial_number' => 'required_if_accepted:use_supplier_serial_number|string|nullable',
            'use_supplier_serial_number' => 'boolean',
        ];
    }
}
