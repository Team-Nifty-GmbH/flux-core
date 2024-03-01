<?php

namespace FluxErp\Rulesets\Communication;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use FluxErp\Models\MailFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateCommunicationRuleset extends FluxRuleset
{
    protected static ?string $model = Communication::class;

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
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(TagRuleset::class, 'getRules')
        );
    }
}
