<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\UpdateMedia;
use Livewire\Attributes\Locked;

class MediaForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $model_type = null;

    public ?int $model_id = null;

    public ?int $parent_id = null;

    public ?string $collection_name = null;

    public ?string $name = null;

    public ?string $file_name = null;

    public ?string $disk = 'public';

    public ?string $media = null;

    public ?string $media_type = null;

    public array $custom_properties = [];

    public array $categories = [];

    public bool $shouldDelete = false;

    protected bool $force = false;

    protected function getActions(): array
    {
        return [
            'update' => UpdateMedia::class,
            'delete' => DeleteMedia::class,
        ];
    }

    public function save(): void
    {
        if ($this->id && $this->shouldDelete) {
            $this->delete();
        } else {
            $this->update();
        }
    }
}
