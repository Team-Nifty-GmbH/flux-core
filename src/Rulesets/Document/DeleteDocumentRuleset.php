<?php

namespace FluxErp\Rulesets\Document;

use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteDocumentRuleset extends FluxRuleset
{
    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Media::class])
                    ->withoutGlobalScope(ExcludeDocumentsScope::class)
                    ->whereNotNull('document_type_id'),
            ],
        ];
    }
}
