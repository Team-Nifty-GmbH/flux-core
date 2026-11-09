<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DocumentType;
use FluxErp\Rulesets\DocumentType\UpdateDocumentTypeRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateDocumentType extends FluxAction
{
    public static function models(): array
    {
        return [DocumentType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateDocumentTypeRuleset::class;
    }

    public function performAction(): Model
    {
        $documentType = resolve_static(DocumentType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $documentType->fill($this->getData());
        $documentType->save();

        return $documentType->withoutRelations()->fresh();
    }
}
