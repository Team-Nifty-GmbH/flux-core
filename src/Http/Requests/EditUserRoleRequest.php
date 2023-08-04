<?php

namespace FluxErp\Http\Requests;

class EditUserRoleRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'sync' => 'sometimes|required|boolean',
            'assign' => 'sometimes|required|boolean',
            'roles' => 'required_without:sync|array',
            'roles.*' => 'required|integer|exists:roles,id',
        ];
    }
}
