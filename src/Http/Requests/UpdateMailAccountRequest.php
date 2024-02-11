<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\MailAccount;
use FluxErp\Rules\ModelExists;

class UpdateMailAccountRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(MailAccount::class),
            ],
            'protocol' => 'sometimes|required|string|max:255|in:imap,pop3,nntp',
            'password' => 'nullable|string|max:255',
            'host' => 'sometimes|required|string|max:255',
            'port' => 'integer',
            'encryption' => 'sometimes|string|max:255|in:ssl,tls',
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
