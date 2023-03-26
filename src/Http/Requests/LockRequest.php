<?php

namespace FluxErp\Http\Requests;

class LockRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lock' => 'sometimes|required|boolean',
        ];
    }
}
