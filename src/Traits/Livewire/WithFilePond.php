<?php

namespace FluxErp\Traits\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;

trait WithFilePond
{
    use WithFileUploads;

    public $files = [];

    public array $latestUploads = [];

    public function getRulesSingleFile(int $index, string $collectionName): array
    {
        // get the base collection name - ignore subfolders
        $baseCollection = str_contains($collectionName, '.') ?
            explode('.', $collectionName)[0] : $collectionName;

        // get the validation rules for the model type
        $mimeTypes = data_get(
            resolve_static($this->modelType, 'query')
                ->whereKey($this->modelId)
                ->first()
                ?->getMediaCollection($baseCollection),
            'acceptsMimeTypes'
        );

        $uploadRules = config('livewire.temporary_file_upload.rules')
            ?? 'file|max:' . min(
                (int) Number::fromFileSizeToBytes(ini_get('upload_max_filesize')),
                (int) Number::fromFileSizeToBytes(ini_get('post_max_size')),
            );

        if (is_array($uploadRules)) {
            $uploadRules = Arr::prepend($uploadRules, 'required');
            if ($mimeTypes) {
                $uploadRules['mimetypes'] = implode(',', $mimeTypes);
            }
        } else {
            $uploadRules = Str::start($uploadRules, 'required|');
            if ($mimeTypes) {
                $uploadRules .= '|mimetypes:' . implode(',', $mimeTypes);
            }
        }

        return [
            'files.' . $index => $uploadRules,
        ];
    }

    #[Renderless]
    public function submitFiles(
        array|string $collection,
        array $tempFileNames,
        ?string $modelType = null,
        ?int $modelId = null
    ): bool {
        $collection = is_array($collection) ? implode('.', $collection) : $collection;

        // set the folder name
        $this->collection = $collection;
        // filter out files array by deleted files on front end
        $this->files = array_filter($this->files, function ($file) use ($tempFileNames) {
            return in_array($file->getFilename(), $tempFileNames);
        });

        // validation took place in updatedFiles method
        // so we can safely save the files
        try {
            $media = $this->saveFileUploadsToMediaLibrary(
                name: 'files',
                modelId: $modelId ?? $this->modelId,
                modelType: $modelType ?? morph_alias($this->modelType),
            );

            $this->latestUploads = $media;
            $this->files = [];
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    #[Renderless]
    public function validateOnDemand(string $fileId, ?string $collectionName = null): bool
    {
        // find the index of the file in the files array - which invokes the validation
        $index = array_search($fileId, array_map(function ($item) {
            return $item->getFilename();
        }, $this->files));

        if ($index === false) {
            return false;
        }

        if (! is_null($collectionName)) {
            try {
                $this->validate($this->getRulesSingleFile($index, $collectionName));
            } catch (ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        }

        return true;
    }
}
