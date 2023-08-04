<?php

namespace FluxErp\Http\Requests;

class EditUserPermissionRequest extends BaseFormRequest
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
            'sync' => 'require_without:give|accepted|boolean',
            'give' => 'required_without:sync|boolean',
            'permissions' => 'present|array',
            'permissions.*' => 'required|integer|exists:permissions,id',
        ];
    }
}
