<?php

namespace FluxErp\Rulesets\MediaFolder;

use FluxErp\Models\MediaFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\InteractsWithMedia;

class UpdateMediaFolderModelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class, ['uses' => InteractsWithMedia::class]),
            ],
            'model_id' => [
                'required',
                'integer',
                app(MorphExists::class),
            ],
            'media_folders' => 'required|array',
            'media_folders.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => MediaFolder::class]),
            ],
            'method' => [
                'required',
                'string',
                'in:attach,detach,sync',
            ],
        ];
    }
}
