<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;

class UpdateWorkTimeTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(WorkTimeType::class),
            ],
            'name' => 'sometimes|required|string',
            'is_billable' => 'boolean',
        ];
    }
}
