<?php

namespace FluxErp\Actions\EmailTemplate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmailTemplate;
use FluxErp\Rulesets\EmailTemplate\DeleteEmailTemplateRuleset;

class DeleteEmailTemplate extends FluxAction
{
    public static function models(): array
    {
        return [EmailTemplate::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteEmailTemplateRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(EmailTemplate::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
