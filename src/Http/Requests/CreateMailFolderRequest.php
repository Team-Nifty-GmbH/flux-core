<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Rules\ModelExists;

class CreateMailFolderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:mail_accounts,uuid',
            'mail_account_id' => [
                'required',
                'integer',
                new ModelExists(MailAccount::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(MailFolder::class),
            ],
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
