<?php

namespace FluxErp\Http\Requests;

class CreatePermissionRequest extends BaseFormRequest
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
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'required|string',
        ];
    }
}
