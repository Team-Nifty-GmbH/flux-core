<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DocumentType;

/**
 * @deprecated
 */
class DeleteDocumentType extends FluxAction
{
    public static function models(): array
    {
        return [DocumentType::class];
    }

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:document_types,id,deleted_at,NULL',
        ];
    }

    public function performAction(): ?bool
    {
        return DocumentType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
