<?php

namespace FluxErp\Rulesets\Media;

use FluxErp\Models\Media;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DownloadMultipleMediaRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'file_name' => [
                'string',
                'nullable',
            ],
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Media::class]),
            ],
        ];
    }
}
