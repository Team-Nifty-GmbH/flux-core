<?php

namespace FluxErp\Rulesets\Media;

use FluxErp\Models\Media;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateMediaRuleset extends FluxRuleset
{
    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'name' => 'sometimes|required|string',
            'file_name' => 'sometimes|required|string',
            'collection' => 'sometimes|required|string',
            'categories' => 'sometimes|array',
            'custom_properties' => 'sometimes|array',
        ];
    }
}
