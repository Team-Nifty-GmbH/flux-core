<?php

namespace FluxErp\Rulesets\MailAccount;

use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\FluxRuleset;

class CreateMailAccountRuleset extends FluxRuleset
{
    protected static ?string $model = MailAccount::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:mail_accounts,uuid',
            'name' => 'required|string|max:255',
            'protocol' => 'nullable|string|max:255|in:imap,pop3,nntp',
            'email' => 'nullable|string|unique:mail_accounts,email',
            'password' => 'nullable|string|max:255',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer',
            'encryption' => 'nullable|string|max:255|in:ssl,tls',
            'smtp_mailer' => 'nullable|string|max:255',
            'smtp_email' => 'nullable|string',
            'smtp_from_name' => 'nullable|string|max:255',
            'smtp_reply_to' => 'nullable|string|email|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_encryption' => 'nullable|string|max:255|in:ssl,tls',
            'is_auto_assign' => 'boolean',
            'is_o_auth' => 'boolean',
            'has_valid_certificate' => 'boolean',
        ];
    }
}
