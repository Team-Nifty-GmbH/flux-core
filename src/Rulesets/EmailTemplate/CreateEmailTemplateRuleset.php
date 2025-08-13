<?php

namespace FluxErp\Rulesets\EmailTemplate;

use FluxErp\Rulesets\FluxRuleset;

class CreateEmailTemplateRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'to' => 'nullable|array',
            'to.*' => 'string|email|distinct',
            'cc' => 'nullable|array',
            'cc.*' => 'string|email|distinct',
            'bcc' => 'nullable|array',
            'bcc.*' => 'string|email|distinct',
            'subject' => 'nullable|string|max:255',
            'html_body' => 'nullable|string',
            'text_body' => 'nullable|string',
        ];
    }
}
