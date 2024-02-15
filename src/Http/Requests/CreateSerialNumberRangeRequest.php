<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Client;
use FluxErp\Models\SerialNumber;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\HasSerialNumberRange;
use Illuminate\Database\Eloquent\Model;

class CreateSerialNumberRangeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new SerialNumber())->hasAdditionalColumnsValidationRules(),
            [
                'uuid' => 'string|uuid|unique:serial_number_ranges,uuid',
                'client_id' => [
                    'required',
                    'integer',
                    new ModelExists(Client::class),
                ],
                'model_type' => [
                    'required',
                    'string',
                    new ClassExists(HasSerialNumberRange::class, Model::class),
                ],
                'model_id' => [
                    'sometimes',
                    'integer',
                    new MorphExists(),
                ],
                'type' => 'required|string',
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
