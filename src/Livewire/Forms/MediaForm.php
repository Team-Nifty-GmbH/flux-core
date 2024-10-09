<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Actions\Media\UploadMedia;
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

    protected function getActions(): array
    {
        return [
            'create' => UploadMedia::class,
            'update' => UpdateMedia::class,
            'delete' => DeleteMedia::class,
            'replace' => ReplaceMedia::class,
        ];
    }

    public function save(): void
    {
        if ($this->id && $this->shouldDelete) {
            $this->delete();
        } elseif ($this->id && $this->media) {
            $this->replace();
        } elseif ($this->id) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public function replace(): void
    {
        $response = $this->makeAction('replace')
            ->validate()
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->execute();

        $this->fill($response);
    }
}
