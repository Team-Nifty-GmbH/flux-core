<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\MediaFolder\CreateMediaFolder;
use FluxErp\Actions\MediaFolder\DeleteMediaFolder;
use FluxErp\Actions\MediaFolder\UpdateMediaFolder;
use Livewire\Attributes\Locked;

class MediaFolderForm extends FluxForm
{
    public ?int $id = null;

    public ?int $parent_id = null;

    public ?string $name = null;

    public ?int $max_files = null;

    public ?array $mime_types = null;

    public bool $is_readonly = false;

    #[Locked]
    public ?int $model_id = null;

    #[Locked]
    public ?string $model_type = null;

    public function getActions(): array
    {
        return [
            'create' => CreateMediaFolder::class,
            'update' => UpdateMediaFolder::class,
            'delete' => DeleteMediaFolder::class,
        ];
    }
}
