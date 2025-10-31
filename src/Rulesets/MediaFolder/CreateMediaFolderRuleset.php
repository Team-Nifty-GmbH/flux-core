<?php

namespace FluxErp\Rulesets\MediaFolder;

use FluxErp\Models\MediaFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\InteractsWithMedia;

class CreateMediaFolderRuleset extends FluxRuleset
{
    protected static ?string $model = MediaFolder::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:prices,uuid',
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => MediaFolder::class]),
            ],
            'name' => 'required|string|max:255',
            'max_files' => 'nullable|integer|min:0',
            'mime_types' => 'nullable|array',
            'is_readonly' => 'boolean',

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
        ];
    }
}
