<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Actions\Media\UploadMedia;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaUploadForm extends MediaForm
{
    public array $stagedFiles = [];

    public array|TemporaryUploadedFile|null $uploadedFile = null;

    protected array|TemporaryUploadedFile|Media|null $file = null;

    protected bool $force = false;

    public function __set(string $name, $value): void
    {
        $this->{$name} = $value;

        if ($name === 'file') {
            $this->generatePreviewUrls();
            $this->uploadedFile = $value;
        }
    }

    public function fill($values): void
    {
        parent::fill($values);

        if ($values instanceof Media) {
            $this->file = $values;

            $this->generatePreviewUrls();
        } elseif ($values instanceof MediaCollection) {
            $this->file = [];
            foreach ($values as $value) {
                $this->file[] = $value;
            }

            $this->generatePreviewUrls();
        }
    }

    public function force(): static
    {
        // this allows saving of media to read-only collections
        $this->force = true;

        return $this;
    }

    public function generatePreviewUrls(): void
    {
        if (is_array($this->file)) {
            foreach ($this->file as $file) {
                $this->stagedFiles[] = $this->arrayFromUpload($file);
            }
        } else {
            $this->stagedFiles = [$this->arrayFromUpload($this->file)];
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

    public function save(): void
    {
        $stagedFiles = collect($this->stagedFiles)->sortBy(fn ($file) => $file['shouldDelete'] ?? false);

        foreach ($stagedFiles as $file) {
            $file = array_intersect_key($file, $this->all());
            $file['id'] = $file['id'] ?? null;

            $this->fill($file);

            if ($this->id && $this->media && ! $this->shouldDelete) {
                $this->replace();
            } elseif ($this->id) {
                parent::save();
            } else {
                $this->create();
            }
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
            'preview_url' => $file->isPreviewable() ? $file->temporaryUrl() : route('icons', ['name' => 'document']),
            'media' => $file->getRealPath(),
        ];
    }

    protected function getActions(): array
    {
        return array_merge(
            parent::getActions(),
            [
                'create' => UploadMedia::class,
                'replace' => ReplaceMedia::class,
            ]
        );
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        return parent::makeAction($name, $data)->force($this->force);
    }
}
