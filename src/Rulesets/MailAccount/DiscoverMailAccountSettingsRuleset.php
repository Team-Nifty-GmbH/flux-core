<?php

namespace FluxErp\Rulesets\MailAccount;

use FluxErp\Rulesets\FluxRuleset;

class DiscoverMailAccountSettingsRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}
