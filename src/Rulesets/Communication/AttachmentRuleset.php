<?php

namespace FluxErp\Rulesets\Communication;

use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rulesets\Media\UploadMediaRuleset;
use Illuminate\Support\Arr;

class AttachmentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return array_merge(
            [
                'attachments' => 'array',
            ],
            Arr::prependKeysWith(
                Arr::except(
                    resolve_static(UploadMediaRuleset::class, 'getRules'),
                    [
                        'model_id',
                        'model_type',
                        'collection_name',
                        'media_type',
                        'parent_id',
                    ]
                ),
                'attachments.*.'
            )
        );
    }
}
