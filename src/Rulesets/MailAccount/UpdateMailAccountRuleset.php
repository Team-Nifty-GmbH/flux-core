<?php

namespace FluxErp\Rulesets\MailAccount;

use FluxErp\Models\MailAccount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateMailAccountRuleset extends FluxRuleset
{
    protected static ?string $model = MailAccount::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => MailAccount::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'protocol' => 'nullable|string|max:255|in:imap,pop3,nntp',
            'password' => 'exclude_if:password,null|string|max:255',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer',
            'encryption' => 'nullable|string|max:255|in:ssl,tls',
            'smtp_mailer' => 'nullable|string|max:255',
            'smtp_email' => 'nullable|string',
            'smtp_from_name' => 'nullable|string|max:255',
            'smtp_reply_to' => 'nullable|string|email|max:255',
            'smtp_user' => 'nullable|string|max:255',
            'smtp_password' => 'exclude_if:smtp_password,null|string|max:255',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_encryption' => 'nullable|string|max:255|in:ssl,tls',
            'is_auto_assign' => 'boolean',
            'is_o_auth' => 'boolean',
            'has_valid_certificate' => 'boolean',
        ];
    }
}
