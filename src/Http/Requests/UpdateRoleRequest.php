<?php

namespace FluxErp\Http\Requests;

class UpdateRoleRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:roles,id',
            'name' => 'sometimes|required|string',
            'permissions' => 'array',
            'permissions.*' => 'required|integer|exists:permissions,id',
        ];
    }
}
