<?php

namespace FluxErp\Http\Requests;

use Illuminate\Support\Arr;

class CreateMailMessageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $mediaRules = (new UploadMediaRequest())->rules();
        unset(
            $mediaRules['model_id'],
            $mediaRules['model_type'],
            $mediaRules['collection_name'],
            $mediaRules['media_type'],
            $mediaRules['parent_id']
        );
        $mediaRules = Arr::prependKeysWith($mediaRules, 'attachments.*.');

        return array_merge(
            $mediaRules,
            [
                'uuid' => 'string|uuid|unique:mail_messages,uuid',
                'mail_account_id' => 'required|integer|exists:mail_accounts,id',
                'mail_folder_id' => 'required|integer|exists:mail_folders,id',
                'message_id' => 'required|string|max:255',
                'message_uid' => 'integer',
                'from' => 'nullable|string|max:255',
                'to' => 'nullable|array',
                'cc' => 'nullable|array',
                'bcc' => 'nullable|array',
                'date' => 'nullable|date',
                'subject' => 'nullable|string|max:255',
                'text_body' => 'nullable|string',
                'html_body' => 'nullable|string',
                'is_seen' => 'boolean',
                'attachments' => 'array',
                'tags' => 'array',
            ]
        );
    }
}
