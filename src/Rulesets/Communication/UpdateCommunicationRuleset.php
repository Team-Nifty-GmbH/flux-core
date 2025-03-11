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

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(TagRuleset::class, 'getRules'),
            resolve_static(CommunicatablesRuleset::class, 'getRules'),
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Communication::class]),
            ],
            'mail_folder_id' => [
                'exclude_unless:communication_type_enum,mail',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => MailFolder::class]),
            ],
            'message_uid' => 'nullable|integer',
            'from' => 'nullable|string|max:255',
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'communication_type_enum' => Rule::enum(CommunicationTypeEnum::class),
            'date' => 'nullable|date',
            'started_at' => 'nullable|date:Y-m-d H:i:s',
            'ended_at' => 'nullable|date:Y-m-d H:i:s|after:started_at',
            'total_time_ms' => 'nullable|integer',
            'subject' => 'string|max:255|nullable',
            'text_body' => 'nullable|string',
            'html_body' => 'nullable|string',
            'is_seen' => 'boolean',
        ];
    }
}
