<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\UpdateMedia;
use Livewire\Attributes\Locked;

class MediaForm extends FluxForm
{
    public array $categories = [];

    public ?string $collection_name = null;

    public array $custom_properties = [];

    public ?string $disk = 'public';

    public ?string $file_name = null;

    #[Locked]
    public ?int $id = null;

    public ?string $media = null;

    public ?string $media_type = null;

    public ?int $model_id = null;

    public ?string $model_type = null;

    public ?string $name = null;

    public ?int $parent_id = null;

    public bool $shouldDelete = false;

    protected bool $force = false;

    public function save(): void
    {
        if ($this->id && $this->shouldDelete) {
            $this->delete();
        } else {
            $this->update();
        }
    }

    protected function getActions(): array
    {
        return [
            'update' => UpdateMedia::class,
            'delete' => DeleteMedia::class,
        ];
    }
}
