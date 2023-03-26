<?php

namespace FluxErp\Http\Requests;

class EditRoleUserRequest extends BaseFormRequest
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
            'users' => 'required|array',
            'users.*' => 'required|integer|exists:users,id,deleted_at,NULL',
        ];
    }
}
