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
            'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
            'sync' => 'require_without:assign|accepted|boolean',
            'assign' => 'required_without:sync|boolean',
            'roles' => 'present|array',
            'roles.*' => 'required|integer|exists:roles,id',
        ];
    }
}
