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
            'to' => 'required|array',
            'to.*' => 'email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'bcc' => 'nullable|array',
            'bcc.*' => 'email',
            'subject' => 'nullable|string|max:255',
            'html_body' => 'nullable|string',
            'text_body' => 'nullable|string',
            'attachments' => 'nullable|array',
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
            'blade_parameters' => 'nullable',
            'blade_parameters_serialized' => 'nullable|boolean',
            'queue' => 'nullable|boolean',
        ];
    }
}
