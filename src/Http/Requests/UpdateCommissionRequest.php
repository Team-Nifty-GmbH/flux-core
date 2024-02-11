<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Commission;
use FluxErp\Rules\ModelExists;

class UpdateCommissionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Commission::class),
            ],
            'commission' => 'required|numeric',
        ];
    }
}
