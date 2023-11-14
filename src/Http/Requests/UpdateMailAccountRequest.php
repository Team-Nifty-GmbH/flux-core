<?php

namespace FluxErp\Http\Requests;

class UpdateMailAccountRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:mail_accounts,id',
            'protocol' => 'sometimes|required|string|max:255|in:imap,pop3,nntp',
            'email' => 'sometimes|required|email|unique:mail_accounts,email',
            'password' => 'sometimes|required|string|max:255',
            'host' => 'sometimes|required|string|max:255',
            'port' => 'integer',
            'encryption' => 'sometimes|string|max:255|in:ssl,tls',
            'is_o_auth' => 'boolean',
            'has_valid_certificate' => 'boolean',
            'smtp_mailer' => 'nullable|string|max:255',
            'smtp_email' => 'email',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'integer',
            'smtp_encryption' => 'nullable|string|max:255|in:ssl,tls',
        ];
    }
}
