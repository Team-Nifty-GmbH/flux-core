<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Rulesets\DocumentType\DeleteDocumentTypeRuleset;
use Illuminate\Validation\ValidationException;

class DeleteDocumentType extends FluxAction
{
    public static function models(): array
    {
        return [DocumentType::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteDocumentTypeRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(DocumentType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->where('document_type_id', $this->getData('id'))
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'id' => [__('The document type is used by documents and cannot be deleted.')],
            ]);
        }
    }
}
