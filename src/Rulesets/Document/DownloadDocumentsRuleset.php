<?php

namespace FluxErp\Rulesets\Document;

use FluxErp\Models\Media;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Model\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class DownloadDocumentsRuleset extends FluxRuleset
{
    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                'max:255',
                app(
                    MorphClassExists::class,
                    ['uses' => InteractsWithMedia::class, 'implements' => HasMedia::class]
                ),
            ],
            'model_ids' => 'required|array',
            'model_ids.*' => 'required|integer',
            'year' => 'nullable|integer|min:1900|max:9999',
        ];
    }
}
