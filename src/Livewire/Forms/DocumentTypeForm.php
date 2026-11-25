<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\DocumentType\CreateDocumentType;
use FluxErp\Actions\DocumentType\DeleteDocumentType;
use FluxErp\Actions\DocumentType\UpdateDocumentType;
use Livewire\Attributes\Locked;

class DocumentTypeForm extends FluxForm
{
    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public bool $is_owner_uploadable = false;

    public array $model_types = [];

    public ?string $name = null;

    public ?int $retention_years = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateDocumentType::class,
            'update' => UpdateDocumentType::class,
            'delete' => DeleteDocumentType::class,
        ];
    }
}
