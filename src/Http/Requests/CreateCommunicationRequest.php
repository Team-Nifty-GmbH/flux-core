<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Tag;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Communicatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class CreateCommunicationRequest extends BaseFormRequest
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
                'uuid' => 'string|uuid|unique:communications,uuid',
                'communicatable_type' => [
                    'required',
                    'string',
                    new ClassExists(uses: Communicatable::class, instanceOf: Model::class),
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
                'message_id' => 'string|nullable|max:255',
                'message_uid' => 'integer',
                'from' => 'nullable|string|max:255',
                'to' => 'nullable|array',
                'cc' => 'nullable|array',
                'bcc' => 'nullable|array',
                'communication_type_enum' => Rule::enum(CommunicationTypeEnum::class),
                'date' => 'nullable|date',
                'subject' => 'nullable|string|max:255',
                'text_body' => 'nullable|string',
                'html_body' => 'nullable|string',
                'is_seen' => 'boolean',

                'attachments' => 'array',

                'tags' => 'array',
                'tags.*' => [
                    'required',
                    'integer',
                    (new ModelExists(Tag::class))->where('type', Communication::class),
                ],
            ]
        );
    }
}
