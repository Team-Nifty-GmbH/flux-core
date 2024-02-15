<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;

class UpdateSerialNumberRangeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new SerialNumber())->hasAdditionalColumnsValidationRules(),
            [
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
            ],
        );
    }
}
