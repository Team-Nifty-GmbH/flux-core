<?php

namespace FluxErp\Rulesets\Communication;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Communicatable;
use Illuminate\Validation\Rule;

class CreateCommunicationRuleset extends FluxRuleset
{
    protected static ?string $model = Communication::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:communications,uuid',
            'communicatable_type' => [
                'required',
                'string',
                new MorphClassExists(uses: Communicatable::class),
            ],
            'communicatable_id' => [
                'required',
                'integer',
                new MorphExists('communicatable_type'),
            ],
            'mail_account_id' => [
                'exclude_unless:communication_type_enum,mail',
                'integer',
                'nullable',
                new ModelExists(MailAccount::class),
            ],
            'mail_folder_id' => [
                'exclude_unless:communication_type_enum,mail',
                'integer',
                'nullable',
                new ModelExists(MailFolder::class),
            ],
            'message_id' => 'nullable|string|max:255',
            'message_uid' => 'integer',
            'from' => 'nullable|string|max:255',
            'to' => 'nullable|array',
            'cc' => 'nullable|array',
            'bcc' => 'nullable|array',
            'communication_type_enum' => Rule::enum(CommunicationTypeEnum::class),
            'date' => 'nullable|date',
            'started_at' => 'required_with:ended_at|nullable|date:Y-m-d H:i:s',
            'ended_at' => 'nullable|date:Y-m-d H:i:s|after:started_at',
            'total_time_ms' => 'nullable|integer',
            'subject' => 'nullable|string|max:255',
            'text_body' => 'nullable|string',
            'html_body' => 'nullable|string',
            'is_seen' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(AttachmentRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules'),
        );
    }
}
