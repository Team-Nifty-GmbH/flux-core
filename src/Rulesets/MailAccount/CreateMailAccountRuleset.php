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
            'uuid' => 'string|uuid|unique:mail_accounts,uuid',
            'protocol' => 'required|string|max:255|in:imap,pop3,nntp',
            'email' => 'required|email|unique:mail_accounts,email',
            'password' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'integer',
            'encryption' => 'string|max:255|in:ssl,tls',
            'smtp_mailer' => 'nullable|string|max:255',
            'smtp_email' => 'email',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'integer',
            'smtp_encryption' => 'nullable|string|max:255|in:ssl,tls',
            'is_auto_assign' => 'boolean',
            'is_o_auth' => 'boolean',
            'has_valid_certificate' => 'boolean',
        ];
    }
}
