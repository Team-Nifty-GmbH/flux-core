<?php

namespace FluxErp\Rulesets\MediaFolder;

use FluxErp\Models\MediaFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\InteractsWithMedia;

class DeleteMediaFolderRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => MediaFolder::class]),
            ],
            'model_type' => [
                'required_with:model_id',
                'string',
                app(MorphClassExists::class, ['uses' => InteractsWithMedia::class]),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                app(MorphExists::class),
            ],
        ];
    }
}
