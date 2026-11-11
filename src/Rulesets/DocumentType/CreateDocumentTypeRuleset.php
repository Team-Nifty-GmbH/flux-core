<?php

namespace FluxErp\Rulesets\DocumentType;

use FluxErp\Models\DocumentType;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;
use Spatie\MediaLibrary\HasMedia;

class CreateDocumentTypeRuleset extends FluxRuleset
{
    protected static ?string $model = DocumentType::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'model_types' => 'nullable|array',
            'model_types.*' => [
                'required',
                'string',
                'distinct',
                app(MorphClassExists::class, ['implements' => HasMedia::class]),
            ],
            'retention_years' => 'nullable|integer|min:1|max:100',
            'is_owner_uploadable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
