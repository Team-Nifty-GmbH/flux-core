<?php

namespace FluxErp\Http\Requests;

class UpdateMailMessageRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:mail_messages,id',
            'mail_account_id' => 'integer|exists:mail_accounts,id',
            'mail_folder_id' => 'integer|exists:mail_folders,id',
            'message_id' => 'string|max:255',
            'message_uid' => 'integer',
            'from' => 'string|max:255',
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'date' => 'date',
            'subject' => 'string|max:255',
            'text_body' => 'string',
            'html_body' => 'string',
            'is_seen' => 'boolean',
            'tags' => 'array',
        ];
    }
}
