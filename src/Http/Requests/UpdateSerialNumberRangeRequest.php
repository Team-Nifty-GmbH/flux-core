<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\SerialNumber;

class UpdateSerialNumberRangeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new SerialNumber())->hasAdditionalColumnsValidationRules(),
            [
                'id' => 'required|integer|exists:serial_number_ranges,id,deleted_at,NULL',
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
