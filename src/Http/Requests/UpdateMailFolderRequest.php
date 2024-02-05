<?php

namespace FluxErp\Http\Requests;

class UpdateMailFolderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:mail_folders,id',
            'parent_id' => 'nullable|integer|exists:mail_folders,id',
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
