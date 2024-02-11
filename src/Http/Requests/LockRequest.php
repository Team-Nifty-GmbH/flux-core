<?php

namespace FluxErp\Http\Requests;

class LockRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'lock' => 'sometimes|required|boolean',
        ];
    }
}
