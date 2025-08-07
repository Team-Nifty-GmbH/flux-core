<?php

namespace FluxErp\Rulesets\MailMessage;

use FluxErp\Models\Client;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\MailAccount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class SendMailRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'mail_account_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => MailAccount::class]),
            ],
            'client_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'template_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => EmailTemplate::class]),
            ],
            'to' => 'required|array',
            'to.*' => 'email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'bcc' => 'nullable|array',
            'bcc.*' => 'email',
            'subject' => 'nullable|string|max:255',
            'text_body' => 'nullable|string',
            'html_body' => 'nullable|string',
            'attachments' => 'nullable|array',
            'blade_parameters' => 'nullable',
            'blade_parameters_serialized' => 'nullable|boolean',
            'queue' => 'boolean',
        ];
    }
}
