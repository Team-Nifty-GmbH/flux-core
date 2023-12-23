<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Actions\Media\UploadMedia;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    // virtual attributes

    public array $stagedFiles = [];

    public bool $shouldDelete = false;

    protected array|TemporaryUploadedFile|Media|null $file = null;

    public array|TemporaryUploadedFile|null $uploadedFile = null;

    protected function getActions(): array
    {
        return [
            'create' => UploadMedia::class,
            'update' => UpdateMedia::class,
            'delete' => DeleteMedia::class,
            'replace' => ReplaceMedia::class,
        ];
    }

    public function generatePreviewUrls(): void
    {
        if (is_array($this->file)) {
            $this->stagedFiles = [];
            foreach ($this->file as $file) {
                $this->stagedFiles[] = $this->arrayFromUpload($file);
            }
        } else {
            $this->stagedFiles = [$this->arrayFromUpload($this->file)];
        }
    }

    public function fill($values): void
    {
        parent::fill($values);

        if ($values instanceof Media) {
            $this->file = $values;

            $this->generatePreviewUrls();
        } elseif ($values instanceof MediaCollection) {
            foreach ($values as $value) {
                $this->file = $value;

                $this->generatePreviewUrls();
            }
        }
    }

    public function save(): void
    {
        $stagedFiles = collect($this->stagedFiles)->sortBy(fn ($file) => $file['shouldDelete'] ?? false);

        foreach ($stagedFiles as $file) {
            $file = array_intersect_key($file, $this->all());
            $this->fill($file);

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
    }

    public function replace(): void
    {
        $response = $this->makeAction('replace')
            ->validate()
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->execute();

        $this->fill($response);
    }

    public function __set(string $name, $value): void
    {
        $this->{$name} = $value;

        if ($name === 'file') {
            $this->generatePreviewUrls();
            $this->uploadedFile = $value;
        }
    }

    protected function arrayFromUpload(TemporaryUploadedFile|Media $file): array
    {
        if ($file instanceof Media) {
            return [
                'id' => $file->getKey(),
                'name' => $file->name,
                'file_name' => $file->file_name,
                'preview_url' => $file->getFullUrl(),
            ];
        }

        return [
            'name' => $file->getClientOriginalName(),
            'temporary_filename' => $file->getFilename(),
            'file_name' => $file->getClientOriginalName(),
            'preview_url' => $file->temporaryUrl(),
            'media' => $file->getRealPath(),
        ];
    }
}
