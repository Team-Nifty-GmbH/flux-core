<?php

namespace FluxErp\Rulesets\Media;

use FluxErp\Models\Media;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteMediaCollectionRuleset extends FluxRuleset
{
    protected static ?string $model = Media::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                new MorphClassExists(),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
            'collection_name' => 'required|string',
        ];
    }
}
