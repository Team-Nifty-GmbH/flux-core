<?php

namespace FluxErp\Rulesets\MediaFolder;

use FluxErp\Models\MediaFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\InteractsWithMedia;

class UpdateMediaFolderRuleset extends FluxRuleset
{
    protected static ?string $model = MediaFolder::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => MediaFolder::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => MediaFolder::class]),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'max_files' => 'nullable|integer|min:0',
            'mime_types' => 'nullable|array',
            'is_readonly' => 'boolean',

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
