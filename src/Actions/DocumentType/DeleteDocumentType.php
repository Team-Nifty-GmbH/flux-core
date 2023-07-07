<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\DocumentType;

class DeleteDocumentType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:document_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function execute(): bool|null
    {
        return DocumentType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
