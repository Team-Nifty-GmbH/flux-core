<?php

namespace FluxErp\Http\Requests;

class CreateRoleRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'guard_name' => $this->guard_name ?? array_keys(config('auth.guards'))[0],
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:roles,name',
            'guard_name' => 'required|string',
            'permissions' => 'array',
            'permissions.*' => 'required|integer|exists:permissions,id',
        ];
    }
}
