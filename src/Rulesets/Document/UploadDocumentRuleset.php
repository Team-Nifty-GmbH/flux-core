<?php

namespace FluxErp\Rulesets\Document;

use FluxErp\Models\DocumentType;
use FluxErp\Models\Media;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Model\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class UploadDocumentRuleset extends FluxRuleset
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
            'model_id' => [
                'required',
                'integer',
                app(MorphExists::class),
            ],
            'name' => 'nullable|string|max:255',
            'document_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => DocumentType::class])
                    ->where('is_active', true),
            ],
            'document_date' => 'required|date',
            'media' => 'required|file',
        ];
    }
}
