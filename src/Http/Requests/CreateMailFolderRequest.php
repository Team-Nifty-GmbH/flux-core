<?php

namespace FluxErp\Http\Requests;

class CreateMailFolderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:mail_accounts,uuid',
            'mail_account_id' => 'required|integer|exists:mail_accounts,id',
            'parent_id' => 'nullable|integer|exists:mail_folders,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
        ];
    }
}
