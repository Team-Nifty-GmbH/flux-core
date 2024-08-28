<?php

namespace FluxErp\Rulesets\User;

use FluxErp\Models\MailAccount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class MailAccountRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'mail_accounts' => 'array',
            'mail_accounts.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => MailAccount::class]),
            ],
        ];
    }
}
