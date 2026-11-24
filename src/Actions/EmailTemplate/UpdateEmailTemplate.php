<?php

namespace FluxErp\Actions\EmailTemplate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmailTemplate;
use FluxErp\Rulesets\EmailTemplate\UpdateEmailTemplateRuleset;

class UpdateEmailTemplate extends FluxAction
{
    protected array $dontTrim = [
        'html_body',
        'text_body',
    ];

    public static function models(): array
    {
        return [EmailTemplate::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateEmailTemplateRuleset::class;
    }

    public function performAction(): EmailTemplate
    {
        $emailTemplate = resolve_static(EmailTemplate::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $emailTemplate->fill($this->getData());
        $emailTemplate->save();

        return $emailTemplate->withoutRelations()->fresh();
    }
}
