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
            'name' => 'sometimes|required|string|max:255',
            'file_name' => 'sometimes|required|string|max:255',
            'collection_name' => 'sometimes|required|string|max:255',
            'categories' => 'sometimes|array',
            'custom_properties' => 'sometimes|array',
        ];
    }
}
