<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class SerialNumberRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'serial_number' => 'array',
            'serial_number.serial_number_range_id' => [
                'exclude_if:serial_number.use_supplier_serial_number,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => SerialNumberRange::class]),
            ],
            'serial_number.serial_number' => [
                'required_with:serial_number',
                'string',
                'max:255',
                'unique:serial_numbers,serial_number',
            ],
            'serial_number.supplier_serial_number' => [
                'required_if_accepted:serial_number.use_supplier_serial_number',
                'string',
                'max:255',
                'nullable',
            ],
            'serial_number.use_supplier_serial_number' => 'boolean',
        ];
    }
}
