<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Document\UploadDocument;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentForm extends FluxForm
{
    public ?string $document_date = null;

    public ?int $document_type_id = null;

    #[Locked]
    public ?int $id = null;

    public ?TemporaryUploadedFile $media = null;

    #[Locked]
    public ?int $model_id = null;

    #[Locked]
    public ?string $model_type = null;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => UploadDocument::class,
        ];
    }
}
