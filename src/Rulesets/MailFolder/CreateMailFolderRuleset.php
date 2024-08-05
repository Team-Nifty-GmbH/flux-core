<?php

namespace FluxErp\Rulesets\MailFolder;

use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateMailFolderRuleset extends FluxRuleset
{
    protected static ?string $model = MailFolder::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:mail_accounts,uuid',
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
