<?php

namespace FluxErp\Rulesets\DocumentType;

use FluxErp\Models\DocumentType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteDocumentTypeRuleset extends FluxRuleset
{
    protected static ?string $model = DocumentType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => DocumentType::class]),
            ],
        ];
    }
}
