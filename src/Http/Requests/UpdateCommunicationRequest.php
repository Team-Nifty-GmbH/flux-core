<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use Illuminate\Validation\Rule;

class UpdateCommunicationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:communications,id,deleted_at,NULL',
            'mail_folder_id' => 'exclude_unless:communication_type_enum,mail|integer|nullable|exists:mail_folders,id',
            'message_uid' => 'integer|nullable',
            'from' => 'string|max:255|nullable',
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'communication_type_enum' => Rule::enum(CommunicationTypeEnum::class),
            'date' => 'date|nullable',
            'subject' => 'string|max:255|nullable',
            'text_body' => 'string|nullable',
            'html_body' => 'string|nullable',
            'is_seen' => 'boolean',

            'tags' => 'array',
            'tags.*' => 'required|integer|exists:tags,id,type,' . Communication::class,
        ];
    }
}
