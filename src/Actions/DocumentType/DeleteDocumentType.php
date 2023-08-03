<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DocumentType;

class DeleteDocumentType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:document_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function performAction(): ?bool
    {
        return DocumentType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
