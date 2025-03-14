<?php

namespace FluxErp\Rulesets\Media;

use FluxErp\Models\Media;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class DownloadMediaRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required_without:model_type',
                'integer',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'model_type' => [
                'exclude_with:id',
                'required_without:id',
                'required_with:model_id',
                'string',
                app(
                    MorphClassExists::class,
                    [
                        'uses' => InteractsWithMedia::class,
                        'implements' => HasMedia::class,
                    ]
                ),
            ],
            'model_id' => [
                'exclude_with:id',
                'required_with:model_type',
                'integer',
                app(MorphExists::class),
            ],
            'file_name' => [
                'exclude_with:id',
                'required_without:id',
                'required_with_all:model_type,model_id',
                'nullable',
                'string',
            ],
            'conversion' => [
                'nullable',
                'string',
            ],
            'as' => [
                'nullable',
                'string',
                'in:base64,url,path,stream',
            ],
        ];
    }
}
