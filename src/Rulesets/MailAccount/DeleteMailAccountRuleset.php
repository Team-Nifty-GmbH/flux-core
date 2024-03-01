<?php

namespace FluxErp\Rulesets\MailAccount;

use FluxErp\Models\MailAccount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteMailAccountRuleset extends FluxRuleset
{
    protected static ?string $model = MailAccount::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(MailAccount::class),
            ],
        ];
    }
}
