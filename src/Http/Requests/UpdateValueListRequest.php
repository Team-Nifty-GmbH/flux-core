<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ModelExists;

class UpdateValueListRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                (new ModelExists(AdditionalColumn::class))->whereNotNull('values'),
            ],
            'name' => 'sometimes|required|string',
            'values' => 'sometimes|required|array',
        ];
    }
}
