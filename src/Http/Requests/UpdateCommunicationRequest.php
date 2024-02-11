<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Tag;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rule;

class UpdateCommunicationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Communication::class),
            ],
            'mail_folder_id' => [
                'exclude_unless:communication_type_enum,mail',
                'integer',
                'nullable',
                new ModelExists(MailFolder::class),
            ],
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
            'tags.*' => [
                'required',
                'integer',
                (new ModelExists(Tag::class))->where('type', Communication::class),
            ],
        ];
    }
}
