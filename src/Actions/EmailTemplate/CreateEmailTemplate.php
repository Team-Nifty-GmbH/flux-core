<?php

namespace FluxErp\Actions\EmailTemplate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmailTemplate;
use FluxErp\Rulesets\EmailTemplate\CreateEmailTemplateRuleset;

class CreateEmailTemplate extends FluxAction
{
    public static function models(): array
    {
        return [EmailTemplate::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateEmailTemplateRuleset::class;
    }

    public function performAction(): EmailTemplate
    {
        $emailTemplate = app(EmailTemplate::class, ['attributes' => $this->getData()]);
        $emailTemplate->save();

        return $emailTemplate->fresh();
    }
}
