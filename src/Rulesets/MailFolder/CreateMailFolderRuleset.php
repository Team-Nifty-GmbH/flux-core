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
                app(ModelExists::class, ['model' => MailAccount::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => MailFolder::class]),
            ],
            'remote_id' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'delta_link' => 'nullable|string',
            'can_create_ticket' => 'boolean',
            'can_create_purchase_invoice' => 'boolean',
            'can_create_lead' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
