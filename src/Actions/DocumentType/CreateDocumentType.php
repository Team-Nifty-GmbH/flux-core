<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DocumentType;
use FluxErp\Rulesets\DocumentType\CreateDocumentTypeRuleset;

class CreateDocumentType extends FluxAction
{
    public static function models(): array
    {
        return [DocumentType::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateDocumentTypeRuleset::class;
    }

    public function performAction(): DocumentType
    {
        $documentType = app(DocumentType::class, ['attributes' => $this->getData()]);
        $documentType->save();

        return $documentType->refresh();
    }
}
